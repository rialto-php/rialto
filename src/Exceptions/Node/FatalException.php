<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

use Symfony\Component\Process\Process;
use ExtractrIo\Rialto\Exceptions\ProcessException;
use ExtractrIo\Rialto\Exceptions\IdentifiesProcess;

class FatalException extends Exception
{
    use IdentifiesProcess;

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
}
