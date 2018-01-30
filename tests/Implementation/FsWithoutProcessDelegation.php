<?php

namespace ExtractrIo\Rialto\Tests\Implementation;

use ExtractrIo\Rialto\AbstractEntryPoint;

class FsWithoutProcessDelegation extends AbstractEntryPoint
{
    public function __construct()
    {
        parent::__construct(__DIR__.'/FsConnectionDelegate.js');
    }
}
