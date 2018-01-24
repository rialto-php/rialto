<?php

namespace ExtractrIo\Rialto\Traits;

trait UsesBasicResourceAsDefault
{
    /**
     * Return the fully qualified name of the defaut resource.
     */
    public function defaultResource(): string
    {
        return \ExtractrIo\Rialto\Data\BasicResource::class;
    }
}
