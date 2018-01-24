<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

use Symfony\Component\Process\Process;
use ExtractrIo\Rialto\Exceptions\IdentifiesProcess;

class FatalException extends Exception
{
    use IdentifiesProcess;

    /**
     * Check if the exception can be applied to the process.
     */
    public static function exceptionApplies(Process $process): bool
    {
        $error = json_decode($process->getErrorOutput(), true);

        return ($error['__rialto_error__'] ?? false) === true;
    }

    /**
     * Constructor.
     */
    public function __construct(Process $process)
    {
        $this->process = $process;

        parent::__construct($process->getErrorOutput());
    }
}
