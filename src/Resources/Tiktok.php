<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\TiktokProfile;
use ScrapingIsNotACrime\Types\TiktokVideo;

final class Tiktok
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /tiktok/profile/{username}. */
    public function profile(string $username): TiktokProfile
    {
        return TiktokProfile::fromArray($this->http->getObject(Routes::tiktokProfile($username)));
    }

    /** GET /tiktok/video/{videoId}. */
    public function video(string $videoId): TiktokVideo
    {
        return TiktokVideo::fromArray($this->http->getObject(Routes::tiktokVideo($videoId)));
    }
}
