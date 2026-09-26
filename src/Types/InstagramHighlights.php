<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/profile/{username}/highlights */
final readonly class InstagramHighlights implements FromArray
{
    /** @param list<InstagramHighlightSummary> $highlights */
    public function __construct(
        public string $username,
        public string $userId,
        public array $highlights,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'username'),
            Read::string($data, 'user_id'),
            Read::objects($data, 'highlights', InstagramHighlightSummary::class),
        );
    }
}
