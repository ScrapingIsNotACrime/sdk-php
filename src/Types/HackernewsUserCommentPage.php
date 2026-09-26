<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /hackernews/users/{username}/comments — same page envelope as the other listings. */
final readonly class HackernewsUserCommentPage implements FromArray
{
    /** @param list<HackernewsUserComment> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public bool $hasMore,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::objects($data, 'items', HackernewsUserComment::class),
            Read::int($data, 'total'),
            Read::int($data, 'page'),
            Read::bool($data, 'has_more'),
        );
    }
}
