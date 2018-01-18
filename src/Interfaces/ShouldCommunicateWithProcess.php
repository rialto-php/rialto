<?php

namespace ExtractrIo\Rialto\Interfaces;

use ExtractrIo\Rialto\Process;

interface ShouldCommunicateWithProcess
{
    /**
     * Set the process.
     *
     * @throws \RuntimeException if the process has already been set.
     */
    public function setProcess(Process $process): void;
}
