<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use Http\Mock\Client as MockClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ScrapingIsNotACrime\Client;
use ScrapingIsNotACrime\GithubTrendingSince;
use ScrapingIsNotACrime\HackernewsFeed;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\Tests\Support\Fake;
use ScrapingIsNotACrime\Tests\Support\TypeGate;
use ScrapingIsNotACrime\Tests\Types\FixtureTypesTest;

/**
 * Every method against the doc's own examples: same fixture data in, same
 * request out. Ported from the Go SDK's contract_test.go, which is the
 * source of truth for the expected request strings.
 */
final class ContractTest extends TestCase
{
    /** @var array<string, string> */
    private const PLACEHOLDERS = [
        'Instagram.Media' => '{"id":"1","shortcode":"C8xQz1aP9Kv"}',
        'TikTok.Video' => '{"id":"7300000000000000000"}',
        'GitHub.Following' => '{"items":[],"total":null,"has_more":false}',
        'HackerNews.Comments' => '{"items":[],"total":0,"page":0,"has_more":false}',
    ];

    /**
     * Each row is [name, fixture id or null, expected request "path?query",
     * items key or null, a callable(Client): mixed].
     *
     * @return iterable<string, array{string, ?string, string, ?string, \Closure(Client): mixed}>
     */
    public static function rows(): iterable
    {
        $rows = [
            ['Instagram.Profile', 'ig-profile', '/instagram/profile/instagram', null,
                static fn(Client $c): mixed => $c->instagram->profile('instagram')],
            ['Instagram.Contact', 'ig-contact', '/instagram/profile/cafedaesquina/contact', null,
                static fn(Client $c): mixed => $c->instagram->contact('cafedaesquina')],
            ['Instagram.LatestPosts', 'ig-timeline', '/instagram/profile/instagram/timeline/latest', null,
                static fn(Client $c): mixed => $c->instagram->latestPosts('instagram')],
            ['Instagram.Posts', 'ig-timeline-paged', '/instagram/profile/nasa/timeline?count=12&cursor=3950671748375397992_528817151', 'medias',
                static fn(Client $c): mixed => $c->instagram->posts('nasa', count: 12, cursor: '3950671748375397992_528817151')],
            ['Instagram.Highlights', 'ig-highlights', '/instagram/profile/nasa/highlights', null,
                static fn(Client $c): mixed => $c->instagram->highlights('nasa')],
            ['Instagram.Highlight', 'ig-highlight-content', '/instagram/highlights/highlight%3A18201653992314974', null,
                static fn(Client $c): mixed => $c->instagram->highlight('highlight:18201653992314974')],
            ['Instagram.MediaByID', 'ig-media-by-id', '/instagram/profile/instagram/media/3123456789012345678', null,
                static fn(Client $c): mixed => $c->instagram->mediaById('instagram', '3123456789012345678')],
            ['Instagram.Media', null, '/instagram/media/C8xQz1aP9Kv', null,
                static fn(Client $c): mixed => $c->instagram->media('C8xQz1aP9Kv')],
            ['Instagram.Download', 'ig-media-download', '/instagram/media/DbtErSrlB2J/download', null,
                static fn(Client $c): mixed => $c->instagram->download('DbtErSrlB2J')],
            ['Instagram.ShortcodeToID', 'ig-shortcode-to-id', '/instagram/media/Dbn-XJhk0_-/id', null,
                static fn(Client $c): mixed => $c->instagram->shortcodeToId('Dbn-XJhk0_-')],
            ['Instagram.IDToShortcode', 'ig-id-to-shortcode', '/instagram/media/id/3956405067326902270', null,
                static fn(Client $c): mixed => $c->instagram->idToShortcode('3956405067326902270')],
            ['Instagram.Reel', 'ig-reels', '/instagram/reels/DyKlMnOpQrS', null,
                static fn(Client $c): mixed => $c->instagram->reel('DyKlMnOpQrS')],
            ['TikTok.Profile', 'tt-profile', '/tiktok/profile/tiktok', null,
                static fn(Client $c): mixed => $c->tiktok->profile('tiktok')],
            ['TikTok.Video', null, '/tiktok/video/7300000000000000000', null,
                static fn(Client $c): mixed => $c->tiktok->video('7300000000000000000')],
            ['YouTube.Videos', 'yt-channel-videos', '/youtube/channel/youtube/videos', null,
                static fn(Client $c): mixed => $c->youtube->videos('youtube')],
            ['AppStore.Search', 'as-search', '/appstore/search?term=instagram&country=us&limit=1', null,
                static fn(Client $c): mixed => $c->appstore->search('instagram', country: 'us', limit: 1)],
            ['AppStore.Reviews', 'as-reviews', '/appstore/reviews?appId=389801252&country=us&page=1', 'reviews',
                static fn(Client $c): mixed => $c->appstore->reviews('389801252', country: 'us', page: 1)],
            ['GitHub.Profile', 'gh-profile', '/github/profiles/torvalds', null,
                static fn(Client $c): mixed => $c->github->profile('torvalds')],
            ['GitHub.Followers', 'gh-followers', '/github/profiles/torvalds/followers?limit=30&page=1', 'items',
                static fn(Client $c): mixed => $c->github->followers('torvalds', limit: 30, page: 1)],
            ['GitHub.Following', null, '/github/profiles/torvalds/following?limit=5&page=1', 'items',
                static fn(Client $c): mixed => $c->github->following('torvalds', limit: 5)],
            ['GitHub.Repositories', 'gh-repos', '/github/profiles/torvalds/repositories?limit=30&page=1', 'items',
                static fn(Client $c): mixed => $c->github->repositories('torvalds', limit: 30)],
            ['GitHub.SearchRepositories', 'gh-search-repos', '/github/repositories?q=stars:%3E10000+language:php&limit=1&page=1', 'items',
                static fn(Client $c): mixed => $c->github->searchRepositories('stars:>10000 language:php', limit: 1)],
            ['GitHub.Trending', 'gh-trending', '/github/trending/repositories?since=weekly&language=php&limit=1', null,
                static fn(Client $c): mixed => $c->github->trending(since: GithubTrendingSince::Weekly, language: 'php', limit: 1)],
            ['HackerNews.Feed', 'hn-feed', '/hackernews/feeds/top?limit=20&page=0', 'items',
                static fn(Client $c): mixed => $c->hackernews->feed(HackernewsFeed::Top, limit: 20, page: 0)],
            ['HackerNews.Item', 'hn-item', '/hackernews/items/8863', null,
                static fn(Client $c): mixed => $c->hackernews->item(8863)],
            ['HackerNews.Search', 'hn-search', '/hackernews/search?q=postgres&limit=20&page=0', 'items',
                static fn(Client $c): mixed => $c->hackernews->search('postgres', limit: 20, page: 0)],
            ['HackerNews.User', 'hn-user', '/hackernews/users/pg', null,
                static fn(Client $c): mixed => $c->hackernews->user('pg')],
            ['HackerNews.Submissions', 'hn-user-submissions', '/hackernews/users/pg/submissions?limit=20&page=0', 'items',
                static fn(Client $c): mixed => $c->hackernews->submissions('pg', limit: 20, page: 0)],
            ['HackerNews.Comments', null, '/hackernews/users/pg/comments?limit=10&page=0', 'items',
                static fn(Client $c): mixed => $c->hackernews->comments('pg', limit: 10)],
            ['Bluesky.Profile', 'bs-profile', '/bluesky/profiles/bsky.app', null,
                static fn(Client $c): mixed => $c->bluesky->profile('bsky.app')],
            ['Bluesky.Posts', 'bs-posts', '/bluesky/profiles/bsky.app/posts?limit=25', 'posts',
                static fn(Client $c): mixed => $c->bluesky->posts('bsky.app', limit: 25)],
            ['Twitch.Profile', 'tw-profile', '/twitch/profiles/ninja', null,
                static fn(Client $c): mixed => $c->twitch->profile('ninja')],
            ['Twitch.Videos', 'tw-videos', '/twitch/profiles/ninja/videos?limit=20', null,
                static fn(Client $c): mixed => $c->twitch->videos('ninja', limit: 20)],
            ['Linktree.Profile', 'lt-profile', '/linktree/profiles/linktree', null,
                static fn(Client $c): mixed => $c->linktree->profile('linktree')],
        ];

        foreach ($rows as $row) {
            yield $row[0] => $row;
        }
    }

    public function testCoversAllMethods(): void
    {
        $names = [];
        $count = 0;
        foreach (self::rows() as $row) {
            $names[$row[0]] = true;
            $count++;
        }
        self::assertCount(34, $names);
        self::assertSame(34, $count);
    }

    /** @param \Closure(Client): mixed $call */
    #[DataProvider('rows')]
    public function testContract(string $name, ?string $fixture, string $request, ?string $itemsKey, \Closure $call): void
    {
        $data = self::dataFor($name, $fixture);
        $mock = new MockClient();
        $mock->addResponse(Fake::json(200, (string) json_encode(['message' => 'ok', 'data' => $data], JSON_THROW_ON_ERROR)));
        $client = new Client('sinac_test', maxRetries: 0, httpClient: $mock);

        $result = $call($client);

        $sent = $mock->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent, $name);
        self::assertRequestMatches($request, $sent, $name);

        if ($result instanceof Page) {
            if ($fixture !== null) {
                self::assertSame([], TypeGate::check($result->data, $data, $name));
            }
            if ($itemsKey !== null) {
                $expectedItems = $data[$itemsKey] ?? [];
                self::assertIsArray($expectedItems, $name);
                self::assertCount(count($expectedItems), $result->items, $name);
            }
        } else {
            self::assertIsObject($result, $name);
            if ($fixture !== null) {
                self::assertSame([], TypeGate::check($result, $data, $name));
            }
        }
    }

    /** @return array<string, mixed> */
    private static function dataFor(string $name, ?string $fixture): array
    {
        if ($fixture !== null) {
            return FixtureTypesTest::fixtureData($fixture);
        }
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode(self::PLACEHOLDERS[$name], true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    private static function assertRequestMatches(string $expected, RequestInterface $request, string $at): void
    {
        [$wantPath, $wantQuery] = array_pad(explode('?', $expected, 2), 2, '');
        $uri = $request->getUri();
        $gotPath = $uri->getPath();
        $gotPath = str_starts_with($gotPath, '/v1') ? substr($gotPath, 3) : $gotPath;
        self::assertSame($wantPath, $gotPath, $at);
        self::assertSame(self::queryPairs($wantQuery), self::queryPairs($uri->getQuery()), $at);
    }

    /** @return list<array{string, string}> */
    private static function queryPairs(string $raw): array
    {
        $pairs = [];
        foreach (explode('&', $raw) as $part) {
            if ($part === '') {
                continue;
            }
            $chunks = array_pad(explode('=', $part, 2), 2, '');
            $pairs[] = [urldecode($chunks[0]), urldecode($chunks[1])];
        }

        return $pairs;
    }

    /**
     * Mirrors the Go SDK's TestPageNextKeepsQueryAndAdvances: for one case
     * per pagination strategy, Next's request keeps the original query and
     * advances the page/cursor.
     *
     * @return iterable<string, array{array<string, mixed>, \Closure(Client): mixed, string}>
     */
    public static function nextCases(): iterable
    {
        yield 'GitHub.SearchRepositories' => [
            ['items' => [['name' => 'a']], 'has_more' => true],
            static fn(Client $c): mixed => $c->github->searchRepositories('go', limit: 1),
            '/github/repositories?q=go&limit=1&page=2',
        ];
        yield 'AppStore.Reviews' => [
            ['reviews' => [['id' => '1']]],
            static fn(Client $c): mixed => $c->appstore->reviews('1', country: 'br'),
            '/appstore/reviews?appId=1&country=br&page=2',
        ];
        yield 'Bluesky.Posts' => [
            ['posts' => [['uri' => 'x']], 'has_more' => true, 'next_cursor' => 'c2'],
            static fn(Client $c): mixed => $c->bluesky->posts('bsky.app', limit: 5),
            '/bluesky/profiles/bsky.app/posts?limit=5&cursor=c2',
        ];
        yield 'HackerNews.Search' => [
            ['items' => [['id' => 1]], 'has_more' => true],
            static fn(Client $c): mixed => $c->hackernews->search('go'),
            '/hackernews/search?q=go&page=1',
        ];
    }

    /**
     * @param array<string, mixed>   $body1
     * @param \Closure(Client): mixed $call
     */
    #[DataProvider('nextCases')]
    public function testNextKeepsQueryAndAdvances(array $body1, \Closure $call, string $want): void
    {
        $mock = new MockClient();
        $mock->addResponse(Fake::json(200, (string) json_encode(['message' => 'ok', 'data' => $body1], JSON_THROW_ON_ERROR)));
        $mock->addResponse(Fake::json(200, (string) json_encode(['message' => 'ok', 'data' => $body1], JSON_THROW_ON_ERROR)));
        $client = new Client('sinac_test', maxRetries: 0, httpClient: $mock);

        $result = $call($client);
        self::assertInstanceOf(Page::class, $result);
        $next = $result->next();
        self::assertNotNull($next);

        self::assertCount(2, $mock->getRequests());
        self::assertRequestMatches($want, $mock->getRequests()[1], $want);
    }
}
