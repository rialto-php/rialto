<?php

declare(strict_types=1);

namespace Nesk\Rialto\Tests;

use Nesk\Rialto\Transport\Curl;
use Nesk\Rialto\Transport\CurlTransport;
use PHPUnit\Framework\TestCase;

final class CurlTransportTest extends TestCase
{
    /** @var Curl&\PHPUnit\Framework\MockObject\MockObject */
    private $curl;

    /** @var CurlTransport */
    private $transport;

    protected function setUp(): void
    {
        $this->curl = $this->createMock(Curl::class);
        $this->transport = new CurlTransport($this->curl);
    }

    public function testTransportMustUseConnectBeforeSend(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('CurlTransport::connect() must be called before CurlTransport::send().');

        $this->transport->send('');
    }

    public function testUriAndTimeoutAreProperlyDefinedOnConnect(): void
    {
        $this->curl
            ->expects(static::atLeastOnce())
            ->method('setoptArray')
            ->with(
                static::callback(function (array $opt): bool {
                    return $opt[\CURLOPT_URL] === 'https://example.net' && $opt[\CURLOPT_TIMEOUT_MS] === 2500;
                })
            );

        $this->transport->connect('https://example.net', 2.5);
    }

    public function testDataPayloadIsProperlyDefinedAndSent(): void
    {
        $this->curl
            ->expects(static::atLeastOnce())
            ->method('setopt')
            ->with(\CURLOPT_POSTFIELDS, 'request payload');

        $this->curl
            ->expects(static::once())
            ->method('exec')
            ->willReturn('response payload');

            $this->transport->connect('https://example.net', 2.5);
        $response = $this->transport->send('request payload');
        static::assertSame('response payload', $response);
    }
}
