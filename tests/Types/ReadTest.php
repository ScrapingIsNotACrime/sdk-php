<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Types;

use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\Types\LinktreeLink;
use ScrapingIsNotACrime\Types\LinktreeProfile;
use ScrapingIsNotACrime\Types\Read;
use ScrapingIsNotACrime\Types\TiktokVideo;

final class ReadTest extends TestCase
{
    public function testWrongTypeFallsBackToDefault(): void
    {
        self::assertSame(0, Read::int(['n' => 'many'], 'n'));
        self::assertSame('', Read::string(['s' => 5], 's'));
        self::assertFalse(Read::bool(['b' => 'yes'], 'b'));
        self::assertNull(Read::nullableString(['s' => 5], 's'));
        self::assertSame([], Read::objects(['l' => 'x'], 'l', LinktreeLink::class));
    }

    public function testPartialObjectKeepsDefaultsForBadFields(): void
    {
        $profile = LinktreeProfile::fromArray(['username' => 'x', 'links' => 'oops', 'is_verified' => 1]);

        self::assertSame('x', $profile->username);
        self::assertSame([], $profile->links);
        self::assertFalse($profile->isVerified);
    }

    public function testIntegralFloatsAndBigInts(): void
    {
        self::assertSame(3, Read::int(['n' => 3.0], 'n'));
        self::assertNull(Read::nullableInt(['n' => 1e30], 'n'));
    }

    public function testTiktokVideoExtras(): void
    {
        $withStringId = TiktokVideo::fromArray(['id' => '7', 'desc' => 'hi']);
        self::assertSame('7', $withStringId->id);
        self::assertSame(['desc' => 'hi'], $withStringId->extra);

        $withNonStringId = TiktokVideo::fromArray(['id' => 123, 'desc' => 'hi']);
        self::assertNull($withNonStringId->id);
        self::assertSame(['id' => 123, 'desc' => 'hi'], $withNonStringId->extra);
    }
}
