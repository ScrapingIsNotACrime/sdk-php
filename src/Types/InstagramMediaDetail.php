<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/**
 * GET /instagram/profile/{username}/media/{mediaId} — the same media object the timeline
 * endpoints return, including the video-only fields when the media is a video.
 */
final readonly class InstagramMediaDetail implements FromArray
{
    public function __construct(
        public string $id,
        public string $shortcode,
        public string $type,
        public int $comments,
        public int $likes,
        /** Null when the post has no caption. */
        public ?string $caption,
        public mixed $location,
        public mixed $thumbnailResources,
        public string $displayUrl,
        public string $takenAtTimestamp,
        public ?int $videoViews,
        public ?string $videoUrl,
        public ?bool $hasAudio,
        public ?InstagramClipsMusicAttribution $clipsMusicAttributionInfo,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'shortcode'),
            Read::string($data, 'type'),
            Read::int($data, 'comments'),
            Read::int($data, 'likes'),
            Read::nullableString($data, 'caption'),
            Read::mixed($data, 'location'),
            Read::mixed($data, 'thumbnail_resources'),
            Read::string($data, 'display_url'),
            Read::string($data, 'taken_at_timestamp'),
            Read::nullableInt($data, 'video_views'),
            Read::nullableString($data, 'video_url'),
            Read::nullableBool($data, 'has_audio'),
            Read::object($data, 'clips_music_attribution_info', InstagramClipsMusicAttribution::class),
        );
    }
}
