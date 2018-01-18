<?php

namespace ExtractrIo\Rialto;

use ExtractrIo\Rialto\Process;
use ExtractrIo\Rialto\Traits\{IdentifiesResource, CommunicatesWithProcess};
use ExtractrIo\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcess};

class BasicResource implements ShouldIdentifyResource, ShouldCommunicateWithProcess
{
    use IdentifiesResource, CommunicatesWithProcess;
}
