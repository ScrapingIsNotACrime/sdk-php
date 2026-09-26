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

    /**
     * A single-process server started only for the stalling test and torn
     * down right after it (tearDown runs even if the test fails).
     *
     * @var resource|null
     */
    private $slowServer = null;
    private int $slowPort = 0;

    public static function setUpBeforeClass(): void
    {
        [self::$server, self::$port] = self::spawnServer();
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server !== null) {
            self::reap(self::$server);
            self::$server = null;
        }
    }

    protected function tearDown(): void
    {
        if ($this->slowServer !== null) {
            self::reap($this->slowServer);
            $this->slowServer = null;
        }
    }

    /**
     * Starts a plain single-process built-in server (no PHP_CLI_SERVER_WORKERS):
     * it has no worker children to orphan, so proc_terminate + proc_close on
     * the returned handle is enough to reap it.
     *
     * @return array{resource, int}
     */
    private static function spawnServer(): array
    {
        $port = random_int(20000, 40000);
        $cmd = [PHP_BINARY, '-S', '127.0.0.1:' . $port, __DIR__ . '/server.php'];
        $process = proc_open($cmd, [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);
        self::assertIsResource($process);
        for ($i = 0; $i < 50; $i++) {
            $socket = @fsockopen('127.0.0.1', $port);
            if ($socket !== false) {
                fclose($socket);

                return [$process, $port];
            }
            usleep(100_000);
        }
        self::fail('built-in server did not start');
    }

    /** @param resource $process */
    private static function reap($process): void
    {
        proc_terminate($process);
        proc_close($process);
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

    /**
     * Runs against its own dedicated server so the 5 s /slow stall never
     * blocks the shared single-process server used by the other tests, and
     * so this server is guaranteed to be reaped in tearDown even on failure.
     *
     * @param 'symfony'|'guzzle' $prefer
     */
    #[DataProvider('clients')]
    public function testTimesOutOnStalledBody(string $prefer): void
    {
        [$this->slowServer, $this->slowPort] = self::spawnServer();
        $client = HttpClientFactory::create(1.0, $prefer);
        $start = microtime(true);
        try {
            $response = $client->sendRequest(new Request('GET', 'http://127.0.0.1:' . $this->slowPort . '/slow'));
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
