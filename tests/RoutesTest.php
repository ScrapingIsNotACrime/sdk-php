<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\HackernewsFeed;
use ScrapingIsNotACrime\Routes;

final class RoutesTest extends TestCase
{
    public function testInvalidSegmentsThrow(): void
    {
        $cases = [
            static fn() => Routes::instagramProfile(''),
            static fn() => Routes::instagramMediaById('nasa', '..'),
            static fn() => Routes::githubFollowers('.', null, null),
            static fn() => Routes::hackernewsFeed('', null, null),
        ];
        foreach ($cases as $i => $case) {
            try {
                $case();
                self::fail("case $i: no exception");
            } catch (\InvalidArgumentException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function testUnknownEnumStringThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Routes::hackernewsFeed('hot', null, null);
    }

    public function testGithubPageDefaultsToOne(): void
    {
        $spec = Routes::githubFollowers('nasa', null, null);
        self::assertSame(1, $spec->page);
        self::assertSame(0, $spec->maxPage);
    }

    public function testHackernewsPageDefaultsToZero(): void
    {
        $spec = Routes::hackernewsFeed(HackernewsFeed::Top, null, null);
        self::assertSame(0, $spec->page);
        self::assertSame(0, $spec->maxPage);
    }

    public function testAppstorePageDefaultsToOneWithMaxPageTen(): void
    {
        $spec = Routes::appstoreReviews('123', null, null);
        self::assertSame(1, $spec->page);
        self::assertSame(10, $spec->maxPage);
    }

    public function testGithubSearchRepositoriesQueryOrder(): void
    {
        $spec = Routes::githubSearchRepositories('go', 1, null);
        self::assertSame([['q', 'go'], ['limit', '1'], ['page', '1']], $spec->route()->query);
    }
}
