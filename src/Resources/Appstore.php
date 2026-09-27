<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\AppstoreReview;
use ScrapingIsNotACrime\Types\AppstoreReviewPage;
use ScrapingIsNotACrime\Types\AppstoreSearch;

final class Appstore
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /appstore/search — country defaults to "us", limit 1-200 (default 10). */
    public function search(string $term, ?string $country = null, ?int $limit = null): AppstoreSearch
    {
        return AppstoreSearch::fromArray($this->http->getObject(Routes::appstoreSearch($term, $country, $limit)));
    }

    /**
     * GET /appstore/reviews — pages 1-10 (Apple's cap); the API returns 400 past page 10.
     *
     * @return Page<AppstoreReview, AppstoreReviewPage>
     */
    public function reviews(string $appId, ?string $country = null, ?int $page = null): Page
    {
        /** @var Page<AppstoreReview, AppstoreReviewPage> */
        return $this->http->page(Routes::appstoreReviews($appId, $country, $page));
    }
}
