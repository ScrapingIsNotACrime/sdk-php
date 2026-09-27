<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /twitch/profiles/{handle}/videos */
final readonly class TwitchVideos implements FromArray
{
    /**
     * @internal
     *
     * @param list<TwitchVideo> $videos
     */
    public function __construct(
        public array $videos,
        public int $count,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::objects($data, 'videos', TwitchVideo::class),
            Read::int($data, 'count'),
        );
    }
}
