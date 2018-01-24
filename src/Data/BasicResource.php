<?php

namespace ExtractrIo\Rialto\Data;

use ExtractrIo\Rialto\Process;
use ExtractrIo\Rialto\Traits\{IdentifiesResource, CommunicatesWithProcess};
use ExtractrIo\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcess};

class BasicResource implements ShouldIdentifyResource, ShouldCommunicateWithProcess, \JsonSerializable
{
    use IdentifiesResource, CommunicatesWithProcess;

    /**
     * Serialize the object to a value that can be serialized natively by {@see json_encode}.
     */
    public function jsonSerialize(): ResourceIdentity
    {
        return $this->getResourceIdentity();
    }
}
