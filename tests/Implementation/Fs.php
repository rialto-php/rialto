<?php

namespace Nesk\Rialto\Tests\Implementation;

use Nesk\Rialto\ProcessSupervisor;
use Nesk\Rialto\AbstractEntryPoint;

class Fs extends AbstractEntryPoint
{
    protected $forbiddenOptions = ['stop_timeout', 'foo'];

    public function __construct(array $userOptions = [])
    {
        $useProcessDelegate = $userOptions['use_process_delegate'] ?? true;
        $defaultOptions = ['eager_by_default' => true];

        parent::__construct(
            __DIR__.'/FsConnectionDelegate.js',
            $useProcessDelegate ? new FsProcessDelegate : null,
            $defaultOptions,
            $userOptions
        );
    }

    public function getProcessSupervisor(): ProcessSupervisor
    {
        return parent::getProcessSupervisor();
    }
}
