<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** One post in a profile's posts feed. */
final readonly class BlueskyPost implements FromArray
{
    /** @internal */
    public function __construct(
        public string $uri,
        public string $cid,
        public string $text,
        public string $author,
        public int $likes,
        public int $reposts,
        public int $replies,
        public int $quotes,
        public string $createdAt,
        public string $indexedAt,
        public string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'uri'),
            Read::string($data, 'cid'),
            Read::string($data, 'text'),
            Read::string($data, 'author'),
            Read::int($data, 'likes'),
            Read::int($data, 'reposts'),
            Read::int($data, 'replies'),
            Read::int($data, 'quotes'),
            Read::string($data, 'created_at'),
            Read::string($data, 'indexed_at'),
            Read::string($data, 'url'),
        );
    }
}
