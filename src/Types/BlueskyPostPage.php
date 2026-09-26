<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /bluesky/profiles/{handle}/posts */
final readonly class BlueskyPostPage implements FromArray
{
    /** @param list<BlueskyPost> $posts */
    public function __construct(
        public array $posts,
        public ?string $nextCursor,
        public bool $hasMore,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::objects($data, 'posts', BlueskyPost::class),
            Read::nullableString($data, 'next_cursor'),
            Read::bool($data, 'has_more'),
        );
    }
}
