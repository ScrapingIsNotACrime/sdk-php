<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** Music attribution for a video/reel; null for original audio. */
final readonly class InstagramClipsMusicAttribution implements FromArray
{
    /** @internal */
    public function __construct(
        public string $artistName,
        public string $songName,
        public bool $usesOriginalAudio,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'artist_name'),
            Read::string($data, 'song_name'),
            Read::bool($data, 'uses_original_audio'),
        );
    }
}
