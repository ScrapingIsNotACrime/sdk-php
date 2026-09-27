<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A comment in a user's comment listing; unlike item-tree nodes, `replies` may be absent. */
final readonly class HackernewsUserComment implements FromArray
{
    /**
     * @internal
     *
     * @param list<HackernewsComment> $replies
     */
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
            Read::objects($data, 'replies', HackernewsComment::class),
        );
    }
}
