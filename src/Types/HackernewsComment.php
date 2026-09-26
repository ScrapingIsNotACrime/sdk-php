<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A comment inside an item's comment tree; replies nest recursively. */
final readonly class HackernewsComment implements FromArray
{
    /** @param list<HackernewsComment> $replies */
    public function __construct(
        public int $id,
        public string $author,
        public string $text,
        public string $createdAt,
        public array $replies,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::int($data, 'id'),
            Read::string($data, 'author'),
            Read::string($data, 'text'),
            Read::string($data, 'created_at'),
            Read::objects($data, 'replies', self::class),
        );
    }
}
