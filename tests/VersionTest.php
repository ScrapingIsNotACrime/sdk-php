<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\Version;

final class VersionTest extends TestCase
{
    public function testUserAgent(): void
    {
        $ua = Version::userAgent();
        self::assertStringStartsWith('scrapingisnotacrime-php/', $ua);
        self::assertStringNotContainsString('/v', $ua);
        self::assertNotSame('', Version::current());
    }
}
