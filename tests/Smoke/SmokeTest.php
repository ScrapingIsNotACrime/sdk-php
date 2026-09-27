<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Smoke;

use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\Client;
use ScrapingIsNotACrime\Config;

/**
 * Run with: SCRAPINGISNOTACRIME_API_KEY=sinac_... ./scripts/dev.sh vendor/bin/phpunit --testsuite smoke
 *
 * Each call is one billed request.
 */
final class SmokeTest extends TestCase
{
    public function testOneCallPerPlatform(): void
    {
        $apiKey = getenv(Config::API_KEY_ENV);
        if ($apiKey === false || trim($apiKey) === '') {
            self::markTestSkipped('set ' . Config::API_KEY_ENV . ' to run the smoke test');
        }

        $client = new Client();

        /** @var array<string, \Closure(): mixed> $calls */
        $calls = [
            'instagram' => static fn(): mixed => $client->instagram->profile('instagram'),
            'tiktok' => static fn(): mixed => $client->tiktok->profile('tiktok'),
            'youtube' => static fn(): mixed => $client->youtube->videos('youtube'),
            'appstore' => static fn(): mixed => $client->appstore->search('instagram', limit: 1),
            'github' => static fn(): mixed => $client->github->profile('torvalds'),
            'hackernews' => static fn(): mixed => $client->hackernews->item(8863),
            'bluesky' => static fn(): mixed => $client->bluesky->profile('bsky.app'),
            'twitch' => static fn(): mixed => $client->twitch->profile('ninja'),
            'linktree' => static fn(): mixed => $client->linktree->profile('linktree'),
        ];

        $failures = [];
        foreach ($calls as $platform => $call) {
            try {
                $call();
            } catch (\Throwable $e) {
                $failures[] = "$platform: {$e->getMessage()}";
            }
        }

        self::assertSame([], $failures);
    }
}
