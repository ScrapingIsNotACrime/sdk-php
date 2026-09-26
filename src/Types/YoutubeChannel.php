<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** The channel block in a channel-videos response. */
final readonly class YoutubeChannel implements FromArray
{
    public function __construct(
        public string $title,
        public string $description,
        public string $externalId,
        public string $avatar,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'title'),
            Read::string($data, 'description'),
            Read::string($data, 'externalId'),
            Read::string($data, 'avatar'),
        );
    }
}
