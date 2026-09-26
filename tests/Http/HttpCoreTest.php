<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Http;

use Http\Mock\Client as MockClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use ScrapingIsNotACrime\Config;
use ScrapingIsNotACrime\Exception\ApiException;
use ScrapingIsNotACrime\Exception\AuthenticationException;
use ScrapingIsNotACrime\Exception\BadRequestException;
use ScrapingIsNotACrime\Exception\ConnectionException;
use ScrapingIsNotACrime\Exception\NotFoundException;
use ScrapingIsNotACrime\Exception\QuotaExceededException;
use ScrapingIsNotACrime\Exception\ScrapingIsNotACrimeException;
use ScrapingIsNotACrime\Exception\UpstreamException;
use ScrapingIsNotACrime\Http\Route;
use ScrapingIsNotACrime\Tests\Support\Fake;

final class HttpCoreTest extends TestCase
{
    public function testSendsHeadersAndReturnsData(): void
    {
        $client = new MockClient();
        $client->addResponse(Fake::json(200, '{"message":"ok","data":{"username":"nasa"}}'));
        $data = Fake::core($client)->get(new Route('/instagram/profile/nasa'));
        self::assertSame(['username' => 'nasa'], $data);
        $request = $client->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame(Config::DEFAULT_BASE_URL . '/instagram/profile/nasa', (string) $request->getUri());
        self::assertSame('GET', $request->getMethod());
        self::assertSame('sinac_test', $request->getHeaderLine('X-Api-Key'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertStringStartsWith('scrapingisnotacrime-php/', $request->getHeaderLine('User-Agent'));
    }

    public function testErrorMapping(): void
    {
        $cases = [
            [404, '{"message":"Profile not found","data":null}', NotFoundException::class, 'Profile not found'],
            [401, '{"message":"Invalid API key"}', AuthenticationException::class, 'Invalid API key'],
            [402, '{"message":"No credits"}', QuotaExceededException::class, 'No credits — see ' . Config::PRICING_URL],
            [500, '<html>oops</html>', ApiException::class, '<html>oops</html>'],
            [404, 'not json', NotFoundException::class, 'not json'],
            [400, '{"message":42}', BadRequestException::class, '{"message":42}'],
            [500, '', ApiException::class, 'empty response body'],
        ];
        foreach ($cases as [$status, $body, $class, $message]) {
            $client = new MockClient();
            $client->addResponse(Fake::json($status, $body, ['X-Request-Id' => 'req_7']));
            try {
                Fake::core($client, 0)->get(new Route('/x'));
                self::fail("$status: no exception");
            } catch (ScrapingIsNotACrimeException $e) {
                self::assertInstanceOf($class, $e, "$status $body");
                self::assertSame($status, $e->status);
                self::assertSame($message, $e->getMessage());
                self::assertSame('req_7', $e->requestId);
            }
        }
    }

    public function testTwoxxWithoutEnvelopeIsApiException(): void
    {
        foreach (['<html>proxy</html>', '{"message":"ok"}', '[1,2]', ''] as $body) {
            $client = new MockClient();
            $client->addResponse(Fake::json(200, $body));
            try {
                Fake::core($client)->get(new Route('/x'));
                self::fail("accepted '$body'");
            } catch (ApiException $e) {
                self::assertSame(200, $e->status);
                self::assertStringStartsWith('unexpected response body', $e->getMessage());
            }
        }
    }

    public function testRedirectIsApiException(): void
    {
        $client = new MockClient();
        $client->addResponse(Fake::json(301, '', ['Location' => 'https://elsewhere.example/v1/x']));
        try {
            Fake::core($client)->get(new Route('/x'));
            self::fail('no exception');
        } catch (ApiException $e) {
            self::assertSame(301, $e->status);
            self::assertSame('redirect to https://elsewhere.example/v1/x not followed', $e->getMessage());
        }
    }

    public function testRetriesRetryableThenSucceeds(): void
    {
        $client = new MockClient();
        $client->addResponse(Fake::json(429, '{"message":"slow down"}', ['Retry-After' => '2']));
        $client->addResponse(Fake::json(502, '{"message":"upstream"}'));
        $client->addResponse(Fake::json(200, '{"data":{"ok":true}}'));
        $waits = [];
        $data = Fake::core($client, 2, $waits)->get(new Route('/x'));
        self::assertSame(['ok' => true], $data);
        self::assertCount(3, $client->getRequests());
        self::assertSame([2.0, 1.0], $waits);
    }

    public function testNoRetryForClientErrorsAndMaxRetries(): void
    {
        $client = new MockClient();
        $client->addResponse(Fake::json(404, '{"message":"nope"}'));
        try {
            Fake::core($client)->get(new Route('/x'));
            self::fail('no exception');
        } catch (NotFoundException) {
            self::assertCount(1, $client->getRequests());
        }

        $client = new MockClient();
        $client->setDefaultResponse(Fake::json(502, '{"message":"down"}'));
        try {
            Fake::core($client, 1)->get(new Route('/x'));
            self::fail('no exception');
        } catch (UpstreamException) {
            self::assertCount(2, $client->getRequests());
        }
    }

    public function testNetworkErrorIsRetriedAndWrapped(): void
    {
        $client = new MockClient();
        $cause = new class ('connection reset') extends \RuntimeException implements NetworkExceptionInterface {
            public function getRequest(): RequestInterface
            {
                return new \Nyholm\Psr7\Request('GET', '/');
            }
        };
        $client->setDefaultException($cause);
        try {
            Fake::core($client, 1)->get(new Route('/x'));
            self::fail('no exception');
        } catch (ConnectionException $e) {
            self::assertSame($cause, $e->getPrevious());
            self::assertNull($e->status);
            self::assertSame('network error: connection reset', $e->getMessage());
            self::assertCount(2, $client->getRequests());
        }
    }

    public function testGetObjectRejectsNonObjectData(): void
    {
        foreach (['[1,2]', '"text"', '3'] as $data) {
            $client = new MockClient();
            $client->addResponse(Fake::json(200, '{"data":' . $data . '}'));
            try {
                Fake::core($client)->getObject(new Route('/x'));
                self::fail("accepted $data");
            } catch (ApiException $e) {
                self::assertStringStartsWith('unexpected response data', $e->getMessage());
            }
        }
        $client = new MockClient();
        $client->addResponse(Fake::json(200, '{"data":{}}'));
        self::assertSame([], Fake::core($client)->getObject(new Route('/x')));
    }
}
