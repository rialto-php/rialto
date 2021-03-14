<?php

declare(strict_types=1);

namespace Nesk\Rialto\Transport;

use function Safe\curl_exec;
use function Safe\curl_init;
use function Safe\curl_setopt;

/**
 * A class to use cURL as an object, which can be easily mocked in tests.
 *
 * @internal
 */
final class Curl
{
    /** @var \CurlHandle */
    private $curl;

    public function __construct(?string $uri = null)
    {
        /** @var \CurlHandle */
        $curl = curl_init($uri);
        $this->curl = $curl;
    }

    /**
     * @param mixed $value
     */
    public function setopt(int $option, $value): void
    {
        curl_setopt($this->getCurlAsResource(), $option, $value);
    }

    /**
     * @param array<int, mixed> $options
     */
    public function setoptArray(array $options): bool
    {
        return \curl_setopt_array($this->curl, $options);
    }

    /**
     * @return bool|string
     */
    public function exec()
    {
        return curl_exec($this->getCurlAsResource());
    }

    /**
     * Return curl instance as a resource. Safe curl_* functions doesn't support \CurlHandle for the moment so we trick
     * PHPStan with this method.
     *
     * @return resource
     */
    private function getCurlAsResource()
    {
        /** @var resource */
        $curl = $this->curl;
        return $curl;
    }
}
