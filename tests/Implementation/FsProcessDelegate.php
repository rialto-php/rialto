<?php

namespace ExtractrIo\Rialto\Tests\Implementation;

use ExtractrIo\Rialto\Traits\UsesBasicResourceAsDefault;
use ExtractrIo\Rialto\Interfaces\ShouldHandleProcessDelegation;

class FsProcessDelegate implements ShouldHandleProcessDelegation
{
    use UsesBasicResourceAsDefault;

    public function resourceFromOriginalClassName(string $className): ?string
    {
        $class = __NAMESPACE__."\\Resources\\$className";

        return class_exists($class) ? $class : null;
    }
}
