<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\BlueskyPost;
use ScrapingIsNotACrime\Types\BlueskyPostPage;
use ScrapingIsNotACrime\Types\BlueskyProfile;

final class Bluesky
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /bluesky/profiles/{handle} — full handle including the domain. */
    public function profile(string $handle): BlueskyProfile
    {
        return BlueskyProfile::fromArray($this->http->getObject(Routes::blueskyProfile($handle)));
    }

    /**
     * GET /bluesky/profiles/{handle}/posts — limit 1-100 (default 25); cursor-paginated.
     *
     * @return Page<BlueskyPost, BlueskyPostPage>
     */
    public function posts(string $handle, ?int $limit = null, ?string $cursor = null): Page
    {
        /** @var Page<BlueskyPost, BlueskyPostPage> */
        return $this->http->page(Routes::blueskyPosts($handle, $limit, $cursor));
    }
}
