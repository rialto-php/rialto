<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

trait HandlesNodeErrors
{
    /**
     * The original stack trace.
     *
     * @var string
     */
    protected $originalTrace;

    /**
     * Determines if the string contains a Node error.
     */
    protected static function isNodeError(string $error): bool
    {
        $error = json_decode($error, true);

        return ($error['__rialto_error__'] ?? false) === true;
    }

    /**
     * Set the original trace and return the message.
     */
    protected function setTraceAndGetMessage($error): string
    {
        $error = is_string($error) ? json_decode($error, true) : $error;

        $this->originalTrace = $error['stack'];

        return $error['message'];
    }

    /**
     * Return the original stack trace.
     */
    public function getOriginalTrace(): string
    {
        return $this->originalTrace;
    }
}
