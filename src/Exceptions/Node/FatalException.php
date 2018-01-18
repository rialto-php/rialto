<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

use Symfony\Component\Process\Process;

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
