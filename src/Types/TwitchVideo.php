<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** One video in a channel's published videos list. */
final readonly class TwitchVideo implements FromArray
{
    public function __construct(
        public string $id,
        public string $title,
        public int $durationSeconds,
        public int $views,
        public string $publishedAt,
        public string $thumbnail,
        public string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'title'),
            Read::int($data, 'duration_seconds'),
            Read::int($data, 'views'),
            Read::string($data, 'published_at'),
            Read::string($data, 'thumbnail'),
            Read::string($data, 'url'),
        );
    }
}
