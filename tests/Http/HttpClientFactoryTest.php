<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Http;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use ScrapingIsNotACrime\Http\HttpClientFactory;

final class HttpClientFactoryTest extends TestCase
{
    /** @var resource|null */
    private static $server = null;
    private static int $port = 0;

    public static function setUpBeforeClass(): void
    {
        self::$port = random_int(20000, 40000);
        $cmd = [PHP_BINARY, '-S', '127.0.0.1:' . self::$port, __DIR__ . '/server.php'];
        $env = array_merge(getenv() ?: [], ['PHP_CLI_SERVER_WORKERS' => '4']);
        $process = proc_open($cmd, [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes, null, $env);
        self::assertIsResource($process);
        self::$server = $process;
        for ($i = 0; $i < 50; $i++) {
            $socket = @fsockopen('127.0.0.1', self::$port);
            if ($socket !== false) {
                fclose($socket);
                return;
            }
            usleep(100_000);
        }
        self::fail('built-in server did not start');
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server !== null) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    /** @return iterable<string, array{string}> */
    public static function clients(): iterable
    {
        yield 'symfony' => ['symfony'];
        yield 'guzzle' => ['guzzle'];
    }

    /** @param 'symfony'|'guzzle' $prefer */
    #[DataProvider('clients')]
    public function testDoesNotFollowRedirects(string $prefer): void
    {
        $client = HttpClientFactory::create(5.0, $prefer);
        $response = $client->sendRequest(new Request('GET', 'http://127.0.0.1:' . self::$port . '/redirect'));
        self::assertSame(302, $response->getStatusCode());
    }

    /** @param 'symfony'|'guzzle' $prefer */
    #[DataProvider('clients')]
    public function testTimesOutOnStalledBody(string $prefer): void
    {
        $client = HttpClientFactory::create(1.0, $prefer);
        $start = microtime(true);
        try {
            $response = $client->sendRequest(new Request('GET', 'http://127.0.0.1:' . self::$port . '/slow'));
            (string) $response->getBody();
            self::fail('no timeout');
        } catch (ClientExceptionInterface|\RuntimeException) {
            self::assertLessThan(4.0, microtime(true) - $start);
        }
    }

    public function testDefaultPrefersSymfony(): void
    {
        self::assertInstanceOf(\Symfony\Component\HttpClient\Psr18Client::class, HttpClientFactory::create(5.0));
    }
}
