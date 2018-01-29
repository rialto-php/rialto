<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

class Exception extends \RuntimeException
{
    use HandlesNodeErrors;

    /**
     * Constructor.
     */
    public function __construct($error)
    {
        parent::__construct($this->setTraceAndGetMessage($error));
    }
}
