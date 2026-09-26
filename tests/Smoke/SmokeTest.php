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

        $client->instagram->profile('instagram');
        $client->tiktok->profile('tiktok');
        $client->youtube->videos('youtube');
        $client->appstore->search('instagram', limit: 1);
        $client->github->profile('torvalds');
        $client->hackernews->item(8863);
        $client->bluesky->profile('bsky.app');
        $client->twitch->profile('ninja');
        $client->linktree->profile('linktree');

        self::addToAssertionCount(1);
    }
}
