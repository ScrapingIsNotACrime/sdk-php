<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

use ScrapingIsNotACrime\Http\PageSpec;
use ScrapingIsNotACrime\Http\Route;

/**
 * One static method per endpoint, returning the Route (or PageSpec, for a
 * paginated endpoint) that describes it. Called by the resource clients.
 *
 * @internal
 */
final class Routes
{
    private function __construct() {}

    // Instagram

    public static function instagramProfile(string $username): Route
    {
        return new Route(Route::path('/instagram/profile/%s', $username));
    }

    public static function instagramContact(string $username): Route
    {
        return new Route(Route::path('/instagram/profile/%s/contact', $username));
    }

    public static function instagramLatestPosts(string $username): Route
    {
        return new Route(Route::path('/instagram/profile/%s/timeline/latest', $username));
    }

    /**
     * @param int|null    $count  posts per page, 1-50 (default 12)
     * @param string|null $cursor from Page::$nextCursor; null for the first page
     */
    public static function instagramPosts(string $username, ?int $count = null, ?string $cursor = null): PageSpec
    {
        return new PageSpec(
            Route::path('/instagram/profile/%s/timeline', $username),
            Route::q('count', $count),
            'cursor',
            'medias',
            $cursor,
            0,
            0,
            Types\InstagramTimelinePage::class,
            'medias',
        );
    }

    public static function instagramHighlights(string $username): Route
    {
        return new Route(Route::path('/instagram/profile/%s/highlights', $username));
    }

    public static function instagramHighlight(string $highlightId): Route
    {
        return new Route(Route::path('/instagram/highlights/%s', $highlightId));
    }

    public static function instagramMediaById(string $username, string $mediaId): Route
    {
        return new Route(Route::path('/instagram/profile/%s/media/%s', $username, $mediaId));
    }

    public static function instagramMedia(string $shortcode): Route
    {
        return new Route(Route::path('/instagram/media/%s', $shortcode));
    }

    public static function instagramDownload(string $shortcode): Route
    {
        return new Route(Route::path('/instagram/media/%s/download', $shortcode));
    }

    public static function instagramShortcodeToId(string $shortcode): Route
    {
        return new Route(Route::path('/instagram/media/%s/id', $shortcode));
    }

    public static function instagramIdToShortcode(string $mediaId): Route
    {
        return new Route(Route::path('/instagram/media/id/%s', $mediaId));
    }

    public static function instagramReel(string $shortcode): Route
    {
        return new Route(Route::path('/instagram/reels/%s', $shortcode));
    }

    // TikTok

    public static function tiktokProfile(string $username): Route
    {
        return new Route(Route::path('/tiktok/profile/%s', $username));
    }

    public static function tiktokVideo(string $videoId): Route
    {
        return new Route(Route::path('/tiktok/video/%s', $videoId));
    }

    // YouTube

    public static function youtubeVideos(string $handle): Route
    {
        return new Route(Route::path('/youtube/channel/%s/videos', $handle));
    }

    // App Store

    /**
     * @param string|null $country two-letter store code (default "us")
     * @param int|null    $limit   1-200 (default 10)
     */
    public static function appstoreSearch(string $term, ?string $country = null, ?int $limit = null): Route
    {
        return new Route('/appstore/search', [
            ['term', $term],
            ...Route::q('country', $country),
            ...Route::q('limit', $limit),
        ]);
    }

    /**
     * @param string|null $country two-letter store code (default "us")
     * @param int|null    $page    1-10 (default 1); Apple's feed caps at 10 pages and the
     *                             payload has no has_more flag
     */
    public static function appstoreReviews(string $appId, ?string $country = null, ?int $page = null): PageSpec
    {
        return new PageSpec(
            '/appstore/reviews',
            [...Route::q('appId', $appId), ...Route::q('country', $country)],
            'numbered',
            'reviews',
            null,
            $page ?? 1,
            10,
            Types\AppstoreReviewPage::class,
            'reviews',
        );
    }

    // GitHub (1-based pages)

    public static function githubProfile(string $handle): Route
    {
        return new Route(Route::path('/github/profiles/%s', $handle));
    }

    /**
     * @param list<array{string, string}> $query      extra query pairs before "limit"
     * @param class-string                $dataClass
     */
    private static function githubList(string $path, array $query, ?int $limit, ?int $page, string $dataClass): PageSpec
    {
        return new PageSpec(
            $path,
            [...$query, ...Route::q('limit', $limit)],
            'numbered',
            'items',
            null,
            $page ?? 1,
            0,
            $dataClass,
            'items',
        );
    }

    /** @param int|null $limit 1-100 (default 30) */
    public static function githubFollowers(string $handle, ?int $limit = null, ?int $page = null): PageSpec
    {
        return self::githubList(Route::path('/github/profiles/%s/followers', $handle), [], $limit, $page, Types\GithubUserPage::class);
    }

    /** @param int|null $limit 1-100 (default 30) */
    public static function githubFollowing(string $handle, ?int $limit = null, ?int $page = null): PageSpec
    {
        return self::githubList(Route::path('/github/profiles/%s/following', $handle), [], $limit, $page, Types\GithubUserPage::class);
    }

    /** @param int|null $limit 1-100 (default 30) */
    public static function githubRepositories(string $handle, ?int $limit = null, ?int $page = null): PageSpec
    {
        return self::githubList(Route::path('/github/profiles/%s/repositories', $handle), [], $limit, $page, Types\GithubRepositoryPage::class);
    }

    /** @param int|null $limit 1-100 (default 30) */
    public static function githubSearchRepositories(string $q, ?int $limit = null, ?int $page = null): PageSpec
    {
        return self::githubList('/github/repositories', Route::q('q', $q), $limit, $page, Types\GithubRepositorySearchPage::class);
    }

    /**
     * @param GithubTrendingSince|string|null $since    default "daily"
     * @param string|null                     $language e.g. "go"
     * @param int|null                        $limit    1-100 (default 30)
     */
    public static function githubTrending(GithubTrendingSince|string|null $since = null, ?string $language = null, ?int $limit = null): Route
    {
        $sinceValue = $since === null ? null : self::resolveSince($since)->value;

        return new Route('/github/trending/repositories', [
            ...Route::q('since', $sinceValue),
            ...Route::q('language', $language),
            ...Route::q('limit', $limit),
        ]);
    }

    private static function resolveSince(GithubTrendingSince|string $since): GithubTrendingSince
    {
        return $since instanceof GithubTrendingSince
            ? $since
            : (GithubTrendingSince::tryFrom($since) ?? throw new \InvalidArgumentException(sprintf('Invalid GithubTrendingSince "%s".', $since)));
    }

    // Hacker News (0-based pages)

    /**
     * @param list<array{string, string}> $query      extra query pairs before "limit"
     * @param class-string                $dataClass
     */
    private static function hackernewsList(string $path, array $query, ?int $limit, ?int $page, string $dataClass): PageSpec
    {
        return new PageSpec(
            $path,
            [...$query, ...Route::q('limit', $limit)],
            'numbered',
            'items',
            null,
            $page ?? 0,
            0,
            $dataClass,
            'items',
        );
    }

    /** @param int|null $limit 1-50 (default 20) */
    public static function hackernewsFeed(HackernewsFeed|string $feed, ?int $limit = null, ?int $page = null): PageSpec
    {
        $resolved = self::resolveFeed($feed);

        return self::hackernewsList(Route::path('/hackernews/feeds/%s', $resolved->value), [], $limit, $page, Types\HackernewsStoryPage::class);
    }

    public static function hackernewsItem(int $id): Route
    {
        return new Route(Route::path('/hackernews/items/%s', (string) $id));
    }

    /** @param int|null $limit 1-50 (default 20) */
    public static function hackernewsSearch(string $q, ?int $limit = null, ?int $page = null): PageSpec
    {
        return self::hackernewsList('/hackernews/search', Route::q('q', $q), $limit, $page, Types\HackernewsStoryPage::class);
    }

    public static function hackernewsUser(string $username): Route
    {
        return new Route(Route::path('/hackernews/users/%s', $username));
    }

    /** @param int|null $limit 1-50 (default 20) */
    public static function hackernewsSubmissions(string $username, ?int $limit = null, ?int $page = null): PageSpec
    {
        return self::hackernewsList(Route::path('/hackernews/users/%s/submissions', $username), [], $limit, $page, Types\HackernewsStoryPage::class);
    }

    /** @param int|null $limit 1-50 (default 20) */
    public static function hackernewsComments(string $username, ?int $limit = null, ?int $page = null): PageSpec
    {
        return self::hackernewsList(Route::path('/hackernews/users/%s/comments', $username), [], $limit, $page, Types\HackernewsUserCommentPage::class);
    }

    private static function resolveFeed(HackernewsFeed|string $feed): HackernewsFeed
    {
        return $feed instanceof HackernewsFeed
            ? $feed
            : (HackernewsFeed::tryFrom($feed) ?? throw new \InvalidArgumentException(sprintf('Invalid HackernewsFeed "%s".', $feed)));
    }

    // Bluesky

    public static function blueskyProfile(string $handle): Route
    {
        return new Route(Route::path('/bluesky/profiles/%s', $handle));
    }

    /**
     * @param int|null    $limit  1-100 (default 25)
     * @param string|null $cursor from Page::$nextCursor; null for the first page
     */
    public static function blueskyPosts(string $handle, ?int $limit = null, ?string $cursor = null): PageSpec
    {
        return new PageSpec(
            Route::path('/bluesky/profiles/%s/posts', $handle),
            Route::q('limit', $limit),
            'cursor',
            'posts',
            $cursor,
            0,
            0,
            Types\BlueskyPostPage::class,
            'posts',
        );
    }

    // Twitch

    public static function twitchProfile(string $handle): Route
    {
        return new Route(Route::path('/twitch/profiles/%s', $handle));
    }

    /** @param int|null $limit 1-100 (default 20) */
    public static function twitchVideos(string $handle, ?int $limit = null): Route
    {
        return (new Route(Route::path('/twitch/profiles/%s/videos', $handle)))->with(Route::q('limit', $limit));
    }

    // Linktree

    public static function linktreeProfile(string $handle): Route
    {
        return new Route(Route::path('/linktree/profiles/%s', $handle));
    }
}
