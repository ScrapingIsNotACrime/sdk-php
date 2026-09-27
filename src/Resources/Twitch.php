<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\TwitchProfile;
use ScrapingIsNotACrime\Types\TwitchVideos;

final class Twitch
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /twitch/profiles/{handle}. */
    public function profile(string $handle): TwitchProfile
    {
        return TwitchProfile::fromArray($this->http->getObject(Routes::twitchProfile($handle)));
    }

    /** GET /twitch/profiles/{handle}/videos — limit 1-100 (default 20). */
    public function videos(string $handle, ?int $limit = null): TwitchVideos
    {
        return TwitchVideos::fromArray($this->http->getObject(Routes::twitchVideos($handle, $limit)));
    }
}
