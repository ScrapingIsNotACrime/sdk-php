<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A highlight reel's summary, as listed on a profile. */
final readonly class InstagramHighlightSummary implements FromArray
{
    public function __construct(
        public string $id,
        public string $title,
        public string $cover,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'title'),
            Read::string($data, 'cover'),
        );
    }
}
