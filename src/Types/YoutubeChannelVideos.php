<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /youtube/channel/{handle}/videos */
final readonly class YoutubeChannelVideos implements FromArray
{
    /**
     * @internal
     *
     * @param list<YoutubeVideo> $videos
     */
    public function __construct(
        public YoutubeChannel $channel,
        public array $videos,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::object($data, 'channel', YoutubeChannel::class) ?? YoutubeChannel::fromArray([]),
            Read::objects($data, 'videos', YoutubeVideo::class),
        );
    }
}
