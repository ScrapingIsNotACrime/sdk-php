<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\GithubTrendingSince;
use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\GithubProfile;
use ScrapingIsNotACrime\Types\GithubRepository;
use ScrapingIsNotACrime\Types\GithubRepositoryPage;
use ScrapingIsNotACrime\Types\GithubRepositorySearchPage;
use ScrapingIsNotACrime\Types\GithubTrending;
use ScrapingIsNotACrime\Types\GithubUser;
use ScrapingIsNotACrime\Types\GithubUserPage;

final class Github
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /github/profiles/{handle}. */
    public function profile(string $handle): GithubProfile
    {
        return GithubProfile::fromArray($this->http->getObject(Routes::githubProfile($handle)));
    }

    /**
     * GET /github/profiles/{handle}/followers — limit 1-100 (default 30); 1-based pages.
     *
     * @return Page<GithubUser, GithubUserPage>
     */
    public function followers(string $handle, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<GithubUser, GithubUserPage> */
        return $this->http->page(Routes::githubFollowers($handle, $limit, $page));
    }

    /**
     * GET /github/profiles/{handle}/following — limit 1-100 (default 30); 1-based pages.
     *
     * @return Page<GithubUser, GithubUserPage>
     */
    public function following(string $handle, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<GithubUser, GithubUserPage> */
        return $this->http->page(Routes::githubFollowing($handle, $limit, $page));
    }

    /**
     * GET /github/profiles/{handle}/repositories — limit 1-100 (default 30); 1-based pages.
     *
     * @return Page<GithubRepository, GithubRepositoryPage>
     */
    public function repositories(string $handle, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<GithubRepository, GithubRepositoryPage> */
        return $this->http->page(Routes::githubRepositories($handle, $limit, $page));
    }

    /**
     * GET /github/repositories — q in GitHub search syntax; limit 1-100 (default 30); 1-based pages.
     *
     * @return Page<GithubRepository, GithubRepositorySearchPage>
     */
    public function searchRepositories(string $q, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<GithubRepository, GithubRepositorySearchPage> */
        return $this->http->page(Routes::githubSearchRepositories($q, $limit, $page));
    }

    /** GET /github/trending/repositories — since defaults to "daily"; limit 1-100 (default 30). */
    public function trending(GithubTrendingSince|string|null $since = null, ?string $language = null, ?int $limit = null): GithubTrending
    {
        return GithubTrending::fromArray($this->http->getObject(Routes::githubTrending($since, $language, $limit)));
    }
}
