<?php

namespace ExtractrIo\Rialto\Exceptions;

use Symfony\Component\Process\Process;

class ProcessUnexpectedlyTerminatedException extends ProcessException
{
    /**
     * Constructor.
     */
    public function __construct(Process $process)
    {
        parent::__construct($process, 'The process has been unexpectedly terminated.');
    }
}
