<?php

namespace Nesk\Rialto\Traits;

use Nesk\Rialto\Data\ResourceIdentity;
use RuntimeException;

trait IdentifiesResource
{
    /**
     * The identity of the resource.
     *
     * @var \Nesk\Rialto\ResourceIdentity
     */
    protected $resourceIdentity;

    /**
     * Return the identity of the resource.
     */
    public function getResourceIdentity(): ?ResourceIdentity
    {
        return $this->resourceIdentity;
    }

    /**
     * Set the identity of the resource.
     *
     * @throws \RuntimeException if the resource identity has already been set.
     */
    public function setResourceIdentity(ResourceIdentity $identity): void
    {
        if ($this->resourceIdentity !== null) {
            throw new RuntimeException('The resource identity has already been set.');
        }

        $this->resourceIdentity = $identity;
    }
}
