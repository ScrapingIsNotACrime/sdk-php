<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Support;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use ScrapingIsNotACrime\Config;
use ScrapingIsNotACrime\Http\HttpCore;

final class Fake
{
    /** @param array<string, string> $headers */
    public static function json(int $status, string $body, array $headers = []): ResponseInterface
    {
        return new Response($status, $headers, $body);
    }

    /**
     * A core over a mock client that never sleeps.
     *
     * @param list<float> $waits receives every backoff wait
     */
    public static function core(MockClient $client, int $maxRetries = 2, array &$waits = [], string $baseUrl = Config::DEFAULT_BASE_URL): HttpCore
    {
        $config = Config::resolve('sinac_test', $baseUrl, 30.0, $maxRetries, []);
        $sleep = static function (float $seconds) use (&$waits): void {
            $waits[] = $seconds;
        };

        return new HttpCore($config, $client, new Psr17Factory(), \Closure::fromCallable($sleep), static fn(): float => 1.0);
    }
}
