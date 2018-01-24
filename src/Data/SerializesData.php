<?php

namespace ExtractrIo\Rialto\Data;

trait SerializesData
{
    /**
     * Serialize a value.
     */
    protected function serialize($value): array
    {
        if ($value instanceof JsFunction) {
            return $value->jsonSerialize();
        }

        return [
            'type' => 'json',
            'value' => $value,
        ];
    }
}
