<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\Config;
use ScrapingIsNotACrime\Exception\ApiException;
use ScrapingIsNotACrime\Exception\AuthenticationException;
use ScrapingIsNotACrime\Exception\BadRequestException;
use ScrapingIsNotACrime\Exception\ConnectionException;
use ScrapingIsNotACrime\Exception\NotFoundException;
use ScrapingIsNotACrime\Exception\QuotaExceededException;
use ScrapingIsNotACrime\Exception\RateLimitException;
use ScrapingIsNotACrime\Exception\ScrapingIsNotACrimeException;
use ScrapingIsNotACrime\Exception\UpstreamException;

final class ExceptionTest extends TestCase
{
    public function testFromStatusPicksClass(): void
    {
        $cases = [
            400 => BadRequestException::class, 401 => AuthenticationException::class,
            402 => QuotaExceededException::class, 404 => NotFoundException::class,
            429 => RateLimitException::class, 502 => UpstreamException::class,
            500 => ApiException::class, 503 => ApiException::class, 418 => ApiException::class,
        ];
        foreach ($cases as $status => $class) {
            $e = ScrapingIsNotACrimeException::fromStatus($status, 'boom', 'req_1');
            self::assertInstanceOf($class, $e, (string) $status);
            self::assertSame($status, $e->status);
            self::assertSame($status, $e->getCode());
            self::assertSame('req_1', $e->requestId);
        }
    }

    public function testQuotaMessageHasPricingUrl(): void
    {
        $e = ScrapingIsNotACrimeException::fromStatus(402, 'No credits left', null);
        self::assertSame('No credits left — see ' . Config::PRICING_URL, $e->getMessage());
    }

    public function testRetryable(): void
    {
        self::assertTrue((new RateLimitException('x', 429))->isRetryable());
        self::assertTrue((new UpstreamException('x', 502))->isRetryable());
        self::assertTrue((new ConnectionException('x'))->isRetryable());
        foreach ([new BadRequestException('x'), new AuthenticationException('x'), new QuotaExceededException('x'),
            new NotFoundException('x'), new ApiException('x')] as $e) {
            self::assertFalse($e->isRetryable(), $e::class);
        }
    }

    public function testConnectionKeepsPrevious(): void
    {
        $cause = new \RuntimeException('socket closed');
        $e = new ConnectionException('network error: socket closed', null, null, $cause);
        self::assertSame($cause, $e->getPrevious());
        self::assertNull($e->status);
        self::assertInstanceOf(\RuntimeException::class, $e);
    }
}
