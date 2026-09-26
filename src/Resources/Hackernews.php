<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\HackernewsFeed;
use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\HackernewsItem;
use ScrapingIsNotACrime\Types\HackernewsStory;
use ScrapingIsNotACrime\Types\HackernewsStoryPage;
use ScrapingIsNotACrime\Types\HackernewsUser;
use ScrapingIsNotACrime\Types\HackernewsUserComment;
use ScrapingIsNotACrime\Types\HackernewsUserCommentPage;

final class Hackernews
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /**
     * GET /hackernews/feeds/{feed} — limit 1-50 (default 20); 0-based pages.
     *
     * @return Page<HackernewsStory, HackernewsStoryPage>
     */
    public function feed(HackernewsFeed|string $feed, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<HackernewsStory, HackernewsStoryPage> */
        return $this->http->page(Routes::hackernewsFeed($feed, $limit, $page));
    }

    /** GET /hackernews/items/{id} — the item with its full comment tree. */
    public function item(int $id): HackernewsItem
    {
        return HackernewsItem::fromArray($this->http->getObject(Routes::hackernewsItem($id)));
    }

    /**
     * GET /hackernews/search — limit 1-50 (default 20); 0-based pages.
     *
     * @return Page<HackernewsStory, HackernewsStoryPage>
     */
    public function search(string $q, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<HackernewsStory, HackernewsStoryPage> */
        return $this->http->page(Routes::hackernewsSearch($q, $limit, $page));
    }

    /** GET /hackernews/users/{username}. */
    public function user(string $username): HackernewsUser
    {
        return HackernewsUser::fromArray($this->http->getObject(Routes::hackernewsUser($username)));
    }

    /**
     * GET /hackernews/users/{username}/submissions — limit 1-50 (default 20); 0-based pages.
     *
     * @return Page<HackernewsStory, HackernewsStoryPage>
     */
    public function submissions(string $username, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<HackernewsStory, HackernewsStoryPage> */
        return $this->http->page(Routes::hackernewsSubmissions($username, $limit, $page));
    }

    /**
     * GET /hackernews/users/{username}/comments — limit 1-50 (default 20); 0-based pages.
     *
     * @return Page<HackernewsUserComment, HackernewsUserCommentPage>
     */
    public function comments(string $username, ?int $limit = null, ?int $page = null): Page
    {
        /** @var Page<HackernewsUserComment, HackernewsUserCommentPage> */
        return $this->http->page(Routes::hackernewsComments($username, $limit, $page));
    }
}
