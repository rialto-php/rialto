<?php

namespace ExtractrIo\Rialto\Tests\Implementation;

use ExtractrIo\Rialto\Process;
use ExtractrIo\Rialto\AbstractEntryPoint;

class Fs extends AbstractEntryPoint
{
    public function __construct(array $options = [])
    {
        $this->createProcess(new FsProcessDelegate, __DIR__.'/FsConnectionDelegate.js', $options);
    }

    public function getProcess(): Process
    {
        return parent::getProcess();
    }
}
