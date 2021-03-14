<?php

declare(strict_types=1);

namespace Nesk\Rialto\Transport;

/**
 * @internal
 */
interface Transport
{
    public function connect(string $uri, float $sendTimeout): void;

    public function send(string $data): string;
}
