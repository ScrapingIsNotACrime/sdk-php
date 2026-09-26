<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\RetryDelay;

final class RetryDelayTest extends TestCase
{
    private const NOW = 1790000000;

    public function testJitter(): void
    {
        $half = static fn(): float => 0.5;
        self::assertSame(0.25, RetryDelay::seconds(0, null, $half, self::NOW));
        self::assertSame(1.0, RetryDelay::seconds(2, null, $half, self::NOW));
        self::assertSame(RetryDelay::MAX, RetryDelay::seconds(10, null, static fn(): float => 1.0, self::NOW));
    }

    public function testRetryAfter(): void
    {
        $zero = static fn(): float => 0.0;
        self::assertSame(3.0, RetryDelay::seconds(0, '3', $zero, self::NOW));
        self::assertSame(1.5, RetryDelay::seconds(0, '1.5', $zero, self::NOW));
        self::assertSame(RetryDelay::MAX, RetryDelay::seconds(0, '99999999999999', $zero, self::NOW));
        $date = gmdate('D, d M Y H:i:s \G\M\T', self::NOW + 4);
        self::assertSame(4.0, RetryDelay::seconds(0, $date, $zero, self::NOW));
        $far = gmdate('D, d M Y H:i:s \G\M\T', self::NOW + 60);
        self::assertSame(RetryDelay::MAX, RetryDelay::seconds(0, $far, $zero, self::NOW));
        $past = gmdate('D, d M Y H:i:s \G\M\T', self::NOW - 60);
        self::assertSame(0.0, RetryDelay::seconds(0, $past, $zero, self::NOW));
        self::assertSame(0.25, RetryDelay::seconds(0, 'soon', static fn(): float => 0.5, self::NOW));
    }
}
