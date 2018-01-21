<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

use Symfony\Component\Process\Process;
use ExtractrIo\Rialto\Exceptions\ProcessException;

class FatalException extends Exception
{
    /**
     * The associated process.
     *
     * @var \Symfony\Component\Process\Process
     */
    private $process;

    /**
     * Constructor.
     */
    public function __construct(Process $process)
    {
        $this->process = $process;

        $error = json_decode($process->getErrorOutput(), true);

        if (($error['__node_communicator_error__'] ?? false) !== true) {
            throw new ProcessException($process);
        }

        parent::__construct($process->getErrorOutput());
    }

    /**
     * Return the associated process.
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}
