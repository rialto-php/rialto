<?php

namespace ExtractrIo\Rialto\Exceptions;

use Symfony\Component\Process\Process;

class ProcessUnexpectedlyTerminatedException extends \RuntimeException
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
        parent::__construct('The process has been unexpectedly terminated.');

        $this->process = $process;
    }

    /**
     * Return the associated process.
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}
