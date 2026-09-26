<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/highlights/{highlightId} */
final readonly class InstagramHighlight implements FromArray
{
    /** @param list<InstagramMedia> $items */
    public function __construct(
        public string $id,
        public string $title,
        public array $items,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'title'),
            Read::objects($data, 'items', InstagramMedia::class),
        );
    }
}
