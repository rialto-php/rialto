<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

use Symfony\Component\Process\Process;
use ExtractrIo\Rialto\Exceptions\IdentifiesProcess;

class FatalException extends Exception
{
    use IdentifiesProcess;

    /**
     * Check if the error output of the process contains a Node exception.
     */
    public static function errorOutputContainsNodeException(Process $process): bool
    {
        $error = json_decode($process->getErrorOutput(), true);

        return ($error['__node_communicator_error__'] ?? false) === true;
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
