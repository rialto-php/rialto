<?php

namespace ExtractrIo\Rialto;

class ResourceIdentity
{
    /**
     * The class name of the resource.
     *
     * @var string
     */
    protected $className;

    /**
     * The unique identifier of the resource.
     *
     * @var string
     */
    protected $uniqueIdentifier;

    /**
     * Constructor.
     */
    public function __construct(string $className, string $uniqueIdentifier)
    {
        $this->className = $className;
        $this->uniqueIdentifier = $uniqueIdentifier;
    }

    /**
     * Return the class name of the resource.
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * Return the unique identifier of the resource.
     */
    public function uniqueIdentifier(): string
    {
        return $this->uniqueIdentifier;
    }
}
