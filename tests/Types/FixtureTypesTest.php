<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Types;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ScrapingIsNotACrime\Tests\Support\TypeGate;
use ScrapingIsNotACrime\Types;

final class FixtureTypesTest extends TestCase
{
    /** @var array<string, class-string> */
    public const FIXTURE_TYPES = [
        'ig-profile' => Types\InstagramProfile::class, 'ig-contact' => Types\InstagramContact::class,
        'ig-timeline' => Types\InstagramLatestPosts::class, 'ig-timeline-paged' => Types\InstagramTimelinePage::class,
        'ig-highlights' => Types\InstagramHighlights::class, 'ig-highlight-content' => Types\InstagramHighlight::class,
        'ig-media-by-id' => Types\InstagramMediaDetail::class, 'ig-media-download' => Types\InstagramDownload::class,
        'ig-shortcode-to-id' => Types\InstagramShortcodeId::class, 'ig-id-to-shortcode' => Types\InstagramShortcodeId::class,
        'ig-reels' => Types\InstagramReel::class, 'tt-profile' => Types\TiktokProfile::class,
        'yt-channel-videos' => Types\YoutubeChannelVideos::class, 'as-search' => Types\AppstoreSearch::class,
        'as-reviews' => Types\AppstoreReviewPage::class, 'gh-profile' => Types\GithubProfile::class,
        'gh-followers' => Types\GithubUserPage::class, 'gh-repos' => Types\GithubRepositoryPage::class,
        'gh-search-repos' => Types\GithubRepositorySearchPage::class, 'gh-trending' => Types\GithubTrending::class,
        'hn-feed' => Types\HackernewsStoryPage::class, 'hn-item' => Types\HackernewsItem::class,
        'hn-search' => Types\HackernewsStoryPage::class, 'hn-user' => Types\HackernewsUser::class,
        'hn-user-submissions' => Types\HackernewsStoryPage::class, 'bs-profile' => Types\BlueskyProfile::class,
        'bs-posts' => Types\BlueskyPostPage::class, 'tw-profile' => Types\TwitchProfile::class,
        'tw-videos' => Types\TwitchVideos::class, 'lt-profile' => Types\LinktreeProfile::class,
    ];

    /** @return array<string, mixed> */
    public static function fixtureData(string $id): array
    {
        $raw = file_get_contents(__DIR__ . '/../fixtures/' . $id . '.json');
        self::assertIsString($raw);
        $fixture = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($fixture);
        $response = $fixture['response'] ?? null;
        self::assertIsArray($response);
        $data = $response['data'] ?? null;
        self::assertIsArray($data);

        /** @var array<string, mixed> $data */
        return $data;
    }

    public function testTableCoversEveryFixture(): void
    {
        $files = glob(__DIR__ . '/../fixtures/*.json') ?: [];
        self::assertCount(30, $files);
        self::assertCount(30, self::FIXTURE_TYPES);
        foreach ($files as $file) {
            self::assertArrayHasKey(basename($file, '.json'), self::FIXTURE_TYPES);
        }
    }

    /** @return iterable<string, array{string, class-string}> */
    public static function fixtures(): iterable
    {
        foreach (self::FIXTURE_TYPES as $id => $class) {
            yield $id => [$id, $class];
        }
    }

    /** @param class-string $class */
    #[DataProvider('fixtures')]
    public function testTypeMatchesDocumentedExample(string $id, string $class): void
    {
        $data = self::fixtureData($id);
        $object = $class::fromArray($data);
        self::assertIsObject($object);
        self::assertSame([], TypeGate::check($object, $data, $id));
    }
}
