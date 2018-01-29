<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

use Symfony\Component\Process\Process;
use ExtractrIo\Rialto\Exceptions\IdentifiesProcess;

class FatalException extends \RuntimeException
{
    use HandlesNodeErrors, IdentifiesProcess;

    /**
     * Check if the exception can be applied to the process.
     */
    public static function exceptionApplies(Process $process): bool
    {
        return static::isNodeError($process->getErrorOutput());
    }

    /**
     * Constructor.
     */
    public function __construct(Process $process)
    {
        $this->process = $process;

        parent::__construct($this->setTraceAndGetMessage($process->getErrorOutput()));
    }
}
