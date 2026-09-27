<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use Http\Discovery\Exception\NotFoundException as DiscoveryNotFoundException;
use Http\Mock\Client as MockClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use ScrapingIsNotACrime\Client;
use ScrapingIsNotACrime\Config;
use ScrapingIsNotACrime\Tests\Support\Fake;

final class ClientTest extends TestCase
{
    private string|false $previousApiKey = false;

    protected function setUp(): void
    {
        $this->previousApiKey = getenv(Config::API_KEY_ENV);
    }

    protected function tearDown(): void
    {
        if ($this->previousApiKey === false) {
            putenv(Config::API_KEY_ENV);
        } else {
            putenv(Config::API_KEY_ENV . '=' . $this->previousApiKey);
        }
    }

    public function testMissingApiKeyThrows(): void
    {
        putenv(Config::API_KEY_ENV);
        $this->expectException(\InvalidArgumentException::class);
        new Client();
    }

    public function testEnvApiKeyIsUsed(): void
    {
        putenv(Config::API_KEY_ENV . '=sinac_env');
        $mock = new MockClient();
        $mock->addResponse(Fake::json(200, '{"message":"ok","data":{"username":"nasa"}}'));
        $client = new Client(maxRetries: 0, httpClient: $mock);

        $client->instagram->profile('nasa');

        $request = $mock->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame('sinac_env', $request->getHeaderLine('X-Api-Key'));
    }

    public function testInvalidArgumentMakesNoRequest(): void
    {
        $mock = new MockClient();
        $client = new Client('sinac_test', maxRetries: 0, httpClient: $mock);

        try {
            $client->instagram->profile('');
            self::fail('instagram->profile(\'\') did not throw');
        } catch (\InvalidArgumentException) {
            self::addToAssertionCount(1);
        }

        try {
            $client->github->followers('..');
            self::fail('github->followers(\'..\') did not throw');
        } catch (\InvalidArgumentException) {
            self::addToAssertionCount(1);
        }

        self::assertCount(0, $mock->getRequests());
    }

    public function testUnknownEnumStringThrows(): void
    {
        $mock = new MockClient();
        $client = new Client('sinac_test', maxRetries: 0, httpClient: $mock);

        $this->expectException(\InvalidArgumentException::class);
        $client->hackernews->feed('hot');
    }

    public function testConstructsWithoutHttpClient(): void
    {
        $client = new Client('sinac_test');
        self::assertInstanceOf(Client::class, $client);
    }

    public function testMissingRequestFactoryThrowsLogicExceptionWithNyholmHint(): void
    {
        $find = static function (): RequestFactoryInterface {
            throw new DiscoveryNotFoundException('no request factory found');
        };

        try {
            Client::requestFactory(null, $find);
            self::fail('did not throw');
        } catch (\LogicException $e) {
            self::assertInstanceOf(DiscoveryNotFoundException::class, $e->getPrevious());
            self::assertStringContainsString('nyholm/psr7', $e->getMessage());
            self::assertStringContainsString('requestFactory', $e->getMessage());
        }
    }
}
