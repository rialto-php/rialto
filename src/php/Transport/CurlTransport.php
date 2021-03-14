<?php

declare(strict_types=1);

namespace Nesk\Rialto\Transport;

/**
 * @internal
 */
final class CurlTransport implements Transport
{
    /** @var Curl */
    private $curl;

    /** @var bool */
    private $isConnected = false;

    public function __construct(?Curl $curl = null)
    {
        $this->curl = $curl ?? new Curl();
    }

    public function connect(string $uri, float $sendTimeout): void
    {
        $this->curl->setoptArray([
            \CURLOPT_URL => $uri,
            \CURLOPT_CUSTOMREQUEST => 'PATCH',
            \CURLOPT_FOLLOWLOCATION => false,
            \CURLOPT_HEADER => false,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_FAILONERROR => true,
            \CURLOPT_NOSIGNAL => true, // See: https://www.php.net/manual/en/function.curl-setopt.php#104597
            \CURLOPT_TIMEOUT_MS => (int) ($sendTimeout * 1000),
        ]);

        $this->isConnected = true;
    }

    public function send(string $data): string
    {
        if (!$this->isConnected) {
            throw new \LogicException('CurlTransport::connect() must be called before CurlTransport::send().');
        }

        $this->curl->setopt(\CURLOPT_POSTFIELDS, $data);

        $payload = $this->curl->exec();
        if (\is_bool($payload)) {
            throw new \LogicException('curl_exec should not return a boolean when CURLOPT_RETURNTRANSFER = true.');
        }

        return $payload;
    }
}
