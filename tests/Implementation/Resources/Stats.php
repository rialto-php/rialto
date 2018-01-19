<?php

namespace ExtractrIo\Rialto\Tests\Implementation\Resources;

use ExtractrIo\Rialto\Traits\{IdentifiesResource, CommunicatesWithProcess};
use ExtractrIo\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcess};

class Stats implements ShouldIdentifyResource, ShouldCommunicateWithProcess
{
    use IdentifiesResource, CommunicatesWithProcess;
}
