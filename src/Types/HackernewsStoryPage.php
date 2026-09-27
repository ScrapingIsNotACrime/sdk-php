<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /hackernews/feeds/{feed}, /hackernews/search, and /hackernews/users/{username}/submissions */
final readonly class HackernewsStoryPage implements FromArray
{
    /**
     * @internal
     *
     * @param list<HackernewsStory> $items
     */
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
            Read::objects($data, 'items', HackernewsStory::class),
            Read::int($data, 'total'),
            Read::int($data, 'page'),
            Read::bool($data, 'has_more'),
        );
    }
}
