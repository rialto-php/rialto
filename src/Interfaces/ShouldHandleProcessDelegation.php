<?php

namespace Nesk\Rialto\Interfaces;

interface ShouldHandleProcessDelegation
{
    /**
     * Return the fully qualified name of the defaut resource.
     */
    public function defaultResource(): string;

    /**
     * Return the fully qualified name of a resource based on the original class name.
     */
    public function resourceFromOriginalClassName(string $className): ?string;
}
