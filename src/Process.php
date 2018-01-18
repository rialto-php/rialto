<?php

namespace ExtractrIo\Rialto;

use RuntimeException;
use Socket\Raw\Socket;
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Exception as SocketException;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ExtractrIo\Rialto\Interfaces\ShouldHandleProcessDelegation;
use ExtractrIo\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcess};

class Process
{
    /**
     * The size of a packet sent through the sockets.
     *
     * @var int
     */
    protected const SOCKET_PACKET_SIZE = 1024;

    /**
     * The size of the header in each packet sent through the sockets.
     *
     * @var int
     */
    protected const SOCKET_HEADER_SIZE = 5;

    /**
     * The associative array containing the options.
     *
     * @var array
     */
    protected $options = [
        // How much time (in seconds) the process can stay inactive before being killed
        'idle_timeout' => 60,

        // How much time (in seconds) an instruction can take to return a value
        'read_timeout' => 30,

        // How much time (in seconds) the process can take to shutdown properly before being killed
        'stop_timeout' => 3,
    ];

    /**
     * The runnning process.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * Whether the process should be stopped on the instance destruction or kept alive.
     *
     * @var bool
     */
    protected $stopProcessOnDestruction = true;

    /**
     * The process delegate.
     *
     * @var \ExtractrIo\Rialto\ShouldHandleProcessDelegation;
     */
    protected $delegate;

    /**
     * The client to communicate with the process.
     *
     * @var \Socket\Raw\Socket
     */
    protected $client;

    /**
     * Constructor.
     */
    public function __construct(
        ShouldHandleProcessDelegation $processDelegate,
        string $connectionDelegatePath,
        array $options = []
    ) {
        $this->options = array_merge($this->options, $options);

        $this->process = $this->createNewProcess($connectionDelegatePath);

        $this->process->start();

        $this->delegate = $processDelegate;

        $this->client = $this->createNewClient($this->serverPort());
    }

    /**
     * Destructor.
     */
    public function __destruct() {
        if ($this->process !== null && $this->stopProcessOnDestruction) {
            $this->process->stop($this->options['stop_timeout']);
        }
    }

    /**
     * Create a new process.
     *
     * @throws RuntimeException if the path to the connection delegate cannot be found.
     */
    protected function createNewProcess(string $connectionDelegatePath): SymfonyProcess
    {
        $realConnectionDelegatePath = realpath($connectionDelegatePath);

        if ($realConnectionDelegatePath === false) {
            throw new RuntimeException("Cannot find file or directory '$connectionDelegatePath'.");
        }

        // Keep only the "idle_timeout" option
        $options = array_intersect_key($this->options, array_flip(['idle_timeout']));

        return new SymfonyProcess(array_merge(
            ['node', __DIR__.'/node-process/serve.js'],
            [$realConnectionDelegatePath],
            [json_encode((object) $options)]
        ));
    }

    /**
     * Keep the process alive even if the PHP instance is destroyed.
     *
     * @return int The PID of the process.
     */
    public function keepProcessAlive(): int
    {
        $this->stopProcessOnDestruction = false;

        return $this->process->getPid();
    }

    /**
     * Check if the process is still running without errors.
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    protected function checkProcessStatus(): void
    {
        $process = $this->process;

        if (!empty($process->getErrorOutput())) {
            throw new Exceptions\Node\FatalException($process);
        }

        if ($process->getExitCode() !== null) {
            throw new Exceptions\ProcessUnexpectedlyTerminatedException($process);
        }

        if ($process->isTerminated()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Return the port of the server.
     */
    protected function serverPort(): int
    {
        $iterator = $this->process->getIterator(SymfonyProcess::ITER_SKIP_ERR | SymfonyProcess::ITER_KEEP_OUTPUT);

        foreach ($iterator as $data) {
            return (int) $data;
        }

        // If the iterator didn't execute properly, then the process must have failed, we must check to be sure.
        $this->checkProcessStatus();
    }

    /**
     * Create a new client to communicate with the process.
     */
    protected function createNewClient(int $port): Socket
    {
        // Set the client as non-blocking to handle the exceptions thrown by the process
        return (new SocketFactory)
            ->createClient("tcp://127.0.0.1:$port")
            ->setBlocking(false);
    }

    /**
     * Send an instruction to the process for execution.
     */
    public function executeInstruction(Instruction $instruction)
    {
        // Check the process status because it could have crash in idle status.
        $this->checkProcessStatus();

        $this->client->selectWrite(1);
        $this->client->write(json_encode($instruction));

        $value = $this->readNextProcessValue();

        // Check the process status if the value is null because, if the process crash while executing the instruction,
        // the socket closes and returns an empty value (which is converted to `null`).
        if ($value === null) {
            $this->checkProcessStatus();
        }

        return $value;
    }

    /**
     * Read the next value written by the process.
     */
    protected function readNextProcessValue()
    {
        $readTimeout = $this->options['read_timeout'];
        $output = '';

        try {
            $this->client->selectRead($readTimeout);

            do {
                $packet = $this->client->read(static::SOCKET_PACKET_SIZE);

                $chunksLeft = (int) substr($packet, 0, static::SOCKET_HEADER_SIZE);
                $chunk = substr($packet, static::SOCKET_HEADER_SIZE);

                $output .= $chunk;
            } while ($chunksLeft > 0);
        } catch (SocketException $exception) {
            // Let the process terminate and output its errors before checking its status
            usleep(200000);
            $this->checkProcessStatus();

            // Extract the socket error code to throw more specific exceptions
            preg_match('/\(([A-Z_]+?)\)$/', $exception->getMessage(), $socketErrorMatches);
            $socketErrorCode = constant($socketErrorMatches[1]);

            switch ($socketErrorCode) {
                case SOCKET_EAGAIN:
                    throw new Exceptions\ReadSocketTimeoutException($readTimeout, $exception);
                default:
                    throw $exception;
            }
        }

        $data = json_decode($output, true);

        return !empty($data) ? $this->unserializeProcessValue($data) : null;
    }

    /**
     * Unserialize a value sent by the process.
     *
     * @throws \ExtractrIo\Rialto\Exceptions\Node\Exception if the process returned an error.
     */
    protected function unserializeProcessValue($value)
    {
        if (!is_array($value)) {
            return $value;
        } else {
            if (($value['__node_communicator_error__'] ?? false) === true) {
                throw new Exceptions\Node\Exception($value['error']);
            } else if (($value['__node_communicator_resource__'] ?? false) === true) {
                $classPath = $this->delegate->resourceFromOriginalClassName($value['class_name'])
                    ?: $this->delegate->defaultResource();

                $resource = new $classPath;

                if ($resource instanceof ShouldIdentifyResource) {
                    $resource->setResourceIdentity(new ResourceIdentity($value['class_name'], $value['id']));
                }

                if ($resource instanceof ShouldCommunicateWithProcess) {
                    $resource->setProcess($this);
                }

                return $resource;
            } else {
                return array_map(function ($value) {
                    return $this->unserializeProcessValue($value);
                }, $value);
            }
        }
    }
}
