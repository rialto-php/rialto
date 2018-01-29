<?php

namespace ExtractrIo\Rialto\Data;

use ExtractrIo\Rialto\Process;
use ExtractrIo\Rialto\Exceptions\Node\Exception;
use ExtractrIo\Rialto\Interfaces\ShouldHandleProcessDelegation;
use ExtractrIo\Rialto\Interfaces\{ShouldIdentifyResource, ShouldCommunicateWithProcess};

trait UnserializesData
{
    /**
     * Unserialize a value.
     */
    protected function unserialize($value)
    {
        if (!is_array($value)) {
            return $value;
        } else {
            if (($value['__rialto_error__'] ?? false) === true) {
                return new Exception($value, $this->options['debug']);
            } else if (($value['__rialto_resource__'] ?? false) === true) {
                $classPath = $this->delegate->resourceFromOriginalClassName($value['class_name'])
                    ?: $this->delegate->defaultResource();

                $resource = new $classPath;

                if ($resource instanceof ShouldIdentifyResource) {
                    $resource->setResourceIdentity(new ResourceIdentity($value['class_name'], $value['id']));
                }

                if ($resource instanceof ShouldCommunicateWithProcess) {
                    $resource->setProcess($this);
                }

                return $resource;
            } else {
                return array_map(function ($value) {
                    return $this->unserialize($value);
                }, $value);
            }
        }
    }
}
