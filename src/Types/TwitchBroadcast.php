<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A channel's most recent broadcast. */
final readonly class TwitchBroadcast implements FromArray
{
    /** @internal */
    public function __construct(
        public string $title,
        public string $startedAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'title'),
            Read::string($data, 'started_at'),
        );
    }
}
