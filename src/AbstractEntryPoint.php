<?php

namespace ExtractrIo\Rialto;

use ExtractrIo\Rialto\Interfaces\ShouldHandleProcessDelegation;

abstract class AbstractEntryPoint
{
    use Traits\CommunicatesWithProcess;

    /**
     * Create the associated process.
     */
    protected function createProcess(
        ShouldHandleProcessDelegation $processDelegate,
        string $connectionDelegatePath,
        array $options = []
    ) {
        $process = new Process($processDelegate, $connectionDelegatePath, $options);

        $this->setProcess($process);
    }
}
