<?php

namespace ExtractrIo\Rialto\Data;

trait SerializesData
{
    /**
     * Serialize a value.
     */
    protected function serialize($value)
    {
        if ($value instanceof JsFunction) {
            return $value->jsonSerialize();
        }

        return $value;
    }
}
