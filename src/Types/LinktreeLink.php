<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** One link in a Linktree profile. */
final readonly class LinktreeLink implements FromArray
{
    /** @internal */
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public string $type,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'title'),
            Read::string($data, 'url'),
            Read::string($data, 'type'),
        );
    }
}
