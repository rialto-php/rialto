<?php

namespace ExtractrIo\Rialto\Exceptions;

use Symfony\Component\Process\Process;

class ProcessException extends \RuntimeException
{
    use IdentifiesProcess;

    /**
     * Constructor.
     */
    public function __construct(Process $process, ?string $message = null)
    {
        parent::__construct($message ?: $process->getErrorOutput());

        $this->process = $process;
    }
}
