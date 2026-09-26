<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Http;

use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\Http\Route;

final class SegmentTest extends TestCase
{
    public function testEncodesLikeEncodeURIComponent(): void
    {
        $cases = [
            'nasa' => 'nasa', 'a/b' => 'a%2Fb', '#tag' => '%23tag', 'é' => '%C3%A9', 'highlight:1' => 'highlight%3A1',
            'a b' => 'a%20b', "-_.!~*'()" => "-_.!~*'()", 'a?b&c=d' => 'a%3Fb%26c%3Dd',
        ];
        foreach ($cases as $in => $want) {
            self::assertSame($want, Route::segment((string) $in), (string) $in);
        }
    }

    public function testRejectsEmptyAndDots(): void
    {
        foreach (['', '.', '..'] as $bad) {
            try {
                Route::segment($bad);
                self::fail("accepted '$bad'");
            } catch (\InvalidArgumentException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function testUrlKeepsOrderAndSkipsNull(): void
    {
        $route = (new Route('/github/repositories'))->with(Route::q('q', 'stars:>1 go'), Route::q('limit', null), Route::q('page', 2));
        self::assertSame('https://api.example.com/v1/github/repositories?q=stars%3A%3E1+go&page=2', $route->url('https://api.example.com/v1'));
        self::assertSame('https://x/v1/a', (new Route('/a'))->url('https://x/v1'));
        self::assertSame('/instagram/profile/nasa/media/1', Route::path('/instagram/profile/%s/media/%s', 'nasa', '1'));
        self::assertSame([['page', '0']], Route::q('page', 0));
    }
}
