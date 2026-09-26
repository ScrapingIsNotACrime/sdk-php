<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** An email address or phone number found written into a profile's bio. */
final readonly class InstagramFoundContact implements FromArray
{
    public function __construct(
        public string $value,
        public string $source,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'value'),
            Read::string($data, 'source'),
        );
    }
}
