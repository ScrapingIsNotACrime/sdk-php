<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/**
 * A post in a profile's latest-posts timeline — an image post or a video
 * post; `type` tells them apart. Video-only fields (videoViews, videoUrl,
 * hasAudio, clipsMusicAttributionInfo) are null for image posts.
 */
final readonly class InstagramLatestPostMedia implements FromArray
{
    public function __construct(
        public string $id,
        public string $shortcode,
        public string $type,
        /** Video-only field. */
        public ?int $videoViews,
        public int $comments,
        public int $likes,
        /** Null when the post has no caption. */
        public ?string $caption,
        public mixed $location,
        public mixed $thumbnailResources,
        public string $displayUrl,
        /** Video-only field. */
        public ?string $videoUrl,
        /** Video-only field. */
        public ?bool $hasAudio,
        /** Video-only field. */
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
            Read::nullableInt($data, 'video_views'),
            Read::int($data, 'comments'),
            Read::int($data, 'likes'),
            Read::nullableString($data, 'caption'),
            Read::mixed($data, 'location'),
            Read::mixed($data, 'thumbnail_resources'),
            Read::string($data, 'display_url'),
            Read::nullableString($data, 'video_url'),
            Read::nullableBool($data, 'has_audio'),
            Read::object($data, 'clips_music_attribution_info', InstagramClipsMusicAttribution::class),
            Read::string($data, 'taken_at_timestamp'),
        );
    }
}
