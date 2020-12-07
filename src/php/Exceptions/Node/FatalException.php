<?php

namespace Nesk\Rialto\Exceptions\Node;

use Nesk\Rialto\Exceptions\IdentifiesProcess;
use Symfony\Component\Process\Process;

class FatalException extends \RuntimeException
{
    use HandlesNodeErrors;
    use IdentifiesProcess;

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
    public function __construct(Process $process, bool $appendStackTraceToMessage = false)
    {
        $this->process = $process;

        $message = $this->setTraceAndGetMessage($process->getErrorOutput(), $appendStackTraceToMessage);

        parent::__construct($message);
    }
}
