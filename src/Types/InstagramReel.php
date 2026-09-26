<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/reels/{shortcode} — clipsMusicAttributionInfo is null for original audio. */
final readonly class InstagramReel implements FromArray
{
    public function __construct(
        public string $id,
        public string $shortcode,
        public string $type,
        public int $videoViews,
        public int $comments,
        public int $likes,
        /** Null when the post has no caption. */
        public ?string $caption,
        public mixed $location,
        public mixed $thumbnailResources,
        public string $displayUrl,
        public string $videoUrl,
        public bool $hasAudio,
        public ?InstagramClipsMusicAttribution $clipsMusicAttributionInfo,
        public string $takenAtTimestamp,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'shortcode'),
            Read::string($data, 'type'),
            Read::int($data, 'video_views'),
            Read::int($data, 'comments'),
            Read::int($data, 'likes'),
            Read::nullableString($data, 'caption'),
            Read::mixed($data, 'location'),
            Read::mixed($data, 'thumbnail_resources'),
            Read::string($data, 'display_url'),
            Read::string($data, 'video_url'),
            Read::bool($data, 'has_audio'),
            Read::object($data, 'clips_music_attribution_info', InstagramClipsMusicAttribution::class),
            Read::string($data, 'taken_at_timestamp'),
        );
    }
}
