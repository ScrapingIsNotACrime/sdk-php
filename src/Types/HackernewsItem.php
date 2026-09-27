<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /hackernews/items/{id} */
final readonly class HackernewsItem implements FromArray
{
    /**
     * @internal
     *
     * @param list<HackernewsComment> $comments
     */
    public function __construct(
        public int $id,
        public string $type,
        /** Null for comments and other untitled item types. */
        public ?string $title,
        public string $author,
        public ?int $points,
        /** External link; null for self-posts. */
        public ?string $url,
        /** Self-post body as HTML; null for link posts. */
        public ?string $text,
        public string $createdAt,
        public string $hnUrl,
        public array $comments,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::int($data, 'id'),
            Read::string($data, 'type'),
            Read::nullableString($data, 'title'),
            Read::string($data, 'author'),
            Read::nullableInt($data, 'points'),
            Read::nullableString($data, 'url'),
            Read::nullableString($data, 'text'),
            Read::string($data, 'created_at'),
            Read::string($data, 'hn_url'),
            Read::objects($data, 'comments', HackernewsComment::class),
        );
    }
}
