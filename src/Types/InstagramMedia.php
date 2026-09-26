<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A post, as returned by the paged timeline and the highlight-content endpoints. */
final readonly class InstagramMedia implements FromArray
{
    /** @param list<mixed> $previewComments */
    public function __construct(
        public string $id,
        public string $shortcode,
        public string $type,
        /** Null when the post has no caption. */
        public ?string $caption,
        public int $likes,
        public int $comments,
        public array $previewComments,
        public mixed $location,
        public string $displayUrl,
        public string $takenAtTimestamp,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'shortcode'),
            Read::string($data, 'type'),
            Read::nullableString($data, 'caption'),
            Read::int($data, 'likes'),
            Read::int($data, 'comments'),
            Read::array($data, 'preview_comments'),
            Read::mixed($data, 'location'),
            Read::string($data, 'display_url'),
            Read::string($data, 'taken_at_timestamp'),
        );
    }
}
