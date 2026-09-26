<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/media/{shortcode}/id and /instagram/media/id/{mediaId} */
final readonly class InstagramShortcodeId implements FromArray
{
    public function __construct(
        public string $shortcode,
        public string $mediaId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'shortcode'),
            Read::string($data, 'media_id'),
        );
    }
}
