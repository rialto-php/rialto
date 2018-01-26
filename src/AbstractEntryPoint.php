<?php

namespace ExtractrIo\Rialto;

use ExtractrIo\Rialto\Interfaces\ShouldHandleProcessDelegation;

abstract class AbstractEntryPoint
{
    use Traits\CommunicatesWithProcess;

    /**
     * Instanciate the entry point of the implementation.
     */
    public function __construct(
        ShouldHandleProcessDelegation $processDelegate,
        string $connectionDelegatePath,
        array $options = []
    ) {
        $process = new Process($processDelegate, $connectionDelegatePath, $options);

        $this->setProcess($process);
    }
}
