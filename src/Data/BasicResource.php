<?php

namespace ExtractrIo\Rialto\Data;

use ExtractrIo\Rialto\Traits\{IdentifiesResource, CommunicatesWithProcessSupervisor};
use ExtractrIo\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcessSupervisor};

class BasicResource implements ShouldIdentifyResource, ShouldCommunicateWithProcessSupervisor, \JsonSerializable
{
    use IdentifiesResource, CommunicatesWithProcessSupervisor;

    /**
     * Serialize the object to a value that can be serialized natively by {@see json_encode}.
     */
    public function jsonSerialize(): ResourceIdentity
    {
        return $this->getResourceIdentity();
    }
}
