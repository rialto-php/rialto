<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

class Exception extends \RuntimeException
{
    /**
     * The original stack trace.
     *
     * @var string
     */
    protected $originalTrace;

    /**
     * Constructor.
     */
    public function __construct($error)
    {
        $error = is_string($error) ? json_decode($error, true) : $error;

        parent::__construct($error['message']);

        $this->originalTrace = $error['stack'];
    }

    /**
     * Return the original stack trace.
     */
    public function getOriginalTrace(): string
    {
        return $this->originalTrace;
    }
}
