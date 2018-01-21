<?php

namespace ExtractrIo\Rialto\Exceptions;

use Symfony\Component\Process\Process;

class ProcessException extends \RuntimeException
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
    public function __construct(Process $process, ?string $message = null)
    {
        parent::__construct($message ?: $process->getErrorOutput());

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
