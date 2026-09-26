<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/media/{shortcode}/download — assets[0] is always the best primary asset. */
final readonly class InstagramDownload implements FromArray
{
    /** @param list<InstagramDownloadAsset> $assets */
    public function __construct(
        public string $shortcode,
        public string $type,
        public string $expiresAt,
        public array $assets,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'shortcode'),
            Read::string($data, 'type'),
            Read::string($data, 'expires_at'),
            Read::objects($data, 'assets', InstagramDownloadAsset::class),
        );
    }
}
