<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\Config;

final class ConfigTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = Config::resolve('  sinac_abc  ', null, 30.0, 2, []);
        self::assertSame('sinac_abc', $config->apiKey);
        self::assertSame(Config::DEFAULT_BASE_URL, $config->baseUrl);
        self::assertSame(30.0, $config->timeout);
        self::assertSame(2, $config->maxRetries);
    }

    public function testEnvKeyAndOverrides(): void
    {
        $config = Config::resolve(null, 'http://localhost:8080/v1///', 5.0, 0, [Config::API_KEY_ENV => 'sinac_env']);
        self::assertSame('sinac_env', $config->apiKey);
        self::assertSame('http://localhost:8080/v1', $config->baseUrl);
        self::assertSame(5.0, $config->timeout);
        self::assertSame(0, $config->maxRetries);
    }

    /** @return iterable<string, array{?string, ?string, float, int}> */
    public static function invalid(): iterable
    {
        yield 'missing key' => [null, null, 30.0, 2];
        yield 'blank key' => ['   ', null, 30.0, 2];
        yield 'zero timeout' => ['k', null, 0.0, 2];
        yield 'negative retries' => ['k', null, 30.0, -1];
        yield 'relative url' => ['k', 'api.example.com/v1', 30.0, 2];
        yield 'ftp url' => ['k', 'ftp://example.com', 30.0, 2];
    }

    #[DataProvider('invalid')]
    public function testRejects(?string $key, ?string $url, float $timeout, int $retries): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Config::resolve($key, $url, $timeout, $retries, []);
    }

    public function testMissingKeyMessage(): void
    {
        try {
            Config::resolve(null, null, 30.0, 2, []);
            self::fail('expected an exception');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('apiKey', $e->getMessage());
            self::assertStringContainsString(Config::API_KEY_ENV, $e->getMessage());
        }
    }
}
