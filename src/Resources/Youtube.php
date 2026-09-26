<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Resources;

use ScrapingIsNotACrime\Http\HttpCore;
use ScrapingIsNotACrime\Routes;
use ScrapingIsNotACrime\Types\YoutubeChannelVideos;

final class Youtube
{
    /** @internal */
    public function __construct(private readonly HttpCore $http) {}

    /** GET /youtube/channel/{handle}/videos. */
    public function videos(string $handle): YoutubeChannelVideos
    {
        return YoutubeChannelVideos::fromArray($this->http->getObject(Routes::youtubeVideos($handle)));
    }
}
