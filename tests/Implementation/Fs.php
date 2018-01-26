<?php

namespace ExtractrIo\Rialto\Tests\Implementation;

use ExtractrIo\Rialto\Process;
use ExtractrIo\Rialto\AbstractEntryPoint;

class Fs extends AbstractEntryPoint
{
    protected $forbiddenOptions = ['stop_timeout', 'foo'];

    public function __construct(array $userOptions = [])
    {
        parent::__construct(new FsProcessDelegate, __DIR__.'/FsConnectionDelegate.js', [], $userOptions);
    }

    public function getProcess(): Process
    {
        return parent::getProcess();
    }
}
