<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/profile/{username}/timeline */
final readonly class InstagramTimelinePage implements FromArray
{
    /** @param list<InstagramMedia> $medias */
    public function __construct(
        public array $medias,
        public bool $hasMore,
        public ?string $nextCursor,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::objects($data, 'medias', InstagramMedia::class),
            Read::bool($data, 'has_more'),
            Read::nullableString($data, 'next_cursor'),
        );
    }
}
