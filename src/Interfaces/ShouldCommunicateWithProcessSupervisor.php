<?php

namespace ExtractrIo\Rialto\Interfaces;

use ExtractrIo\Rialto\ProcessSupervisor;

interface ShouldCommunicateWithProcessSupervisor
{
    /**
     * Set the process supervisor.
     *
     * @throws \RuntimeException if the process has already been set.
     */
    public function setProcessSupervisor(ProcessSupervisor $process): void;
}
