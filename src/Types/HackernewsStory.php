<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A story, as returned by feeds, search, and a user's submissions. */
final readonly class HackernewsStory implements FromArray
{
    /** @internal */
    public function __construct(
        public int $id,
        public string $title,
        public string $author,
        public int $points,
        public int $numComments,
        /** External link; null for self-posts (Ask HN, etc.). */
        public ?string $url,
        /** Self-post body as HTML; null for link posts. */
        public ?string $text,
        public string $createdAt,
        public string $hnUrl,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::int($data, 'id'),
            Read::string($data, 'title'),
            Read::string($data, 'author'),
            Read::int($data, 'points'),
            Read::int($data, 'num_comments'),
            Read::nullableString($data, 'url'),
            Read::nullableString($data, 'text'),
            Read::string($data, 'created_at'),
            Read::string($data, 'hn_url'),
        );
    }
}
