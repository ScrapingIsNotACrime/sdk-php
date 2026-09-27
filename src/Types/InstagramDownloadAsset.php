<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** One downloadable asset behind a post, reel, or carousel. */
final readonly class InstagramDownloadAsset implements FromArray
{
    /** @internal */
    public function __construct(
        public string $kind,
        public int $index,
        public string $url,
        public int $width,
        public int $height,
        /** Null for non-video assets (e.g. thumbnails). */
        public ?string $quality,
        public string $expiresAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'kind'),
            Read::int($data, 'index'),
            Read::string($data, 'url'),
            Read::int($data, 'width'),
            Read::int($data, 'height'),
            Read::nullableString($data, 'quality'),
            Read::string($data, 'expires_at'),
        );
    }
}
