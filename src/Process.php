<?php

namespace ExtractrIo\Rialto;

use RuntimeException;
use Socket\Raw\Socket;
use Psr\Log\{LoggerInterface, LogLevel};
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Exception as SocketException;
use ExtractrIo\Rialto\Exceptions\IdleTimeoutException;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ExtractrIo\Rialto\Interfaces\ShouldHandleProcessDelegation;
use ExtractrIo\Rialto\Exceptions\Node\Exception as NodeException;
use ExtractrIo\Rialto\Exceptions\Node\FatalException as NodeFatalException;

class Process
{
    use Data\UnserializesData;

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
        // Node's executable path
        'executable_path' => 'node',

        // How much time (in seconds) the process can stay inactive before being killed
        'idle_timeout' => 60,

        // How much time (in seconds) an instruction can take to return a value
        'read_timeout' => 30,

        // How much time (in seconds) the process can take to shutdown properly before being killed
        'stop_timeout' => 3,

        // A logger instance for debugging (must implement \Psr\Log\LoggerInterface)
        'logger' => null,

        // Enables debugging mode (adds the --inspect flag to Node's command)
        'debug' => false,
    ];

    /**
     * The running process.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * The PID of the running process.
     *
     * @var int
     */
    protected $processPid;

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
     * The server port.
     *
     * @var int
     */
    protected $serverPort;

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

        $this->processPid = $this->startProcess($this->process);

        $this->delegate = $processDelegate;

        $this->client = $this->createNewClient($this->serverPort());
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->process !== null) {
            $this->log(LogLevel::DEBUG, ["PID {$this->processPid}"], 'Stopping process...');

            $this->process->stop($this->options['stop_timeout']);

            $this->log(LogLevel::DEBUG, ["PID {$this->processPid}"], 'Stopped process');
        }
    }

    /**
     * Log a message with an arbitrary level.
     */
    protected function log(string $level, array $sections, string $message): bool
    {
        ['logger' => $logger] = $this->options;

        if ($logger instanceof LoggerInterface) {
            $sections = implode(' ', array_map(function ($section) {
                return "[$section]";
            }, $sections));

            $logger->log($level, empty($sections) ? $message : "$sections $message");

            return true;
        }

        return false;
    }

    /**
     * Create a new Node process.
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
            [$this->options['executable_path']],
            $this->options['debug'] ? ['--inspect'] : [],
            [__DIR__.'/node-process/serve.js'],
            [$realConnectionDelegatePath],
            [json_encode((object) $options)]
        ));
    }

    /**
     * Start the Node process.
     */
    protected function startProcess(SymfonyProcess $process): int
    {
        $this->log(LogLevel::DEBUG, [], 'Starting process...');

        $process->start();

        $pid = $process->getPid();

        $this->log(LogLevel::DEBUG, ["PID $pid"], 'Process started');

        return $pid;
    }

    /**
     * Check if the process is still running without errors.
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    protected function checkProcessStatus(): void
    {
        $process = $this->process;

        if (!empty($output = $process->getIncrementalOutput())) {
            $this->log(LogLevel::NOTICE, ["PID {$this->processPid}", "stdout"], $output);
        }

        if (!empty($errorOutput = $process->getIncrementalErrorOutput())) {
            $this->log(LogLevel::ERROR, ["PID {$this->processPid}", "stderr"], $errorOutput);
        }

        if (!empty($process->getErrorOutput())) {
            if (IdleTimeoutException::exceptionApplies($process)) {
                throw new IdleTimeoutException($this->options['idle_timeout'], new NodeFatalException($process));
            } else if (NodeFatalException::exceptionApplies($process)) {
                throw new NodeFatalException($process);
            } elseif ($process->isTerminated() && !$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }

        if ($process->isTerminated()) {
            throw new Exceptions\ProcessUnexpectedlyTerminatedException($process);
        }
    }

    /**
     * Return the port of the server.
     */
    protected function serverPort(): int
    {
        if ($this->serverPort !== null) {
            return $this->serverPort;
        }

        $iterator = $this->process->getIterator(SymfonyProcess::ITER_SKIP_ERR | SymfonyProcess::ITER_KEEP_OUTPUT);

        foreach ($iterator as $data) {
            return $this->serverPort = (int) $data;
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

        $instruction = json_encode($instruction);
        $this->log(LogLevel::DEBUG, ["PORT {$this->serverPort()}", "sending"], $instruction);

        $this->client->selectWrite(1);
        $this->client->write($instruction);

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
     *
     * @throws \ExtractrIo\Rialto\Exceptions\Node\Exception if the process returned an error.
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

        $this->log(LogLevel::DEBUG, ["PORT {$this->serverPort()}", "receiving"], $output);

        $data = json_decode($output, true);

        $value = !empty($data) ? $this->unserialize($data) : null;

        if ($value instanceof NodeException) {
            throw $value;
        }

        return $value;
    }
}
