<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** One app in an App Store search result. */
final readonly class AppstoreApp implements FromArray
{
    /**
     * @internal
     *
     * @param list<string> $genres
     * @param list<string> $screenshots
     */
    public function __construct(
        public int $id,
        public string $bundleId,
        public string $name,
        public string $developer,
        public string $url,
        public string $iconUrl,
        public float $price,
        public string $currency,
        public float $rating,
        public int $ratingCount,
        public string $version,
        public array $genres,
        public array $screenshots,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::int($data, 'id'),
            Read::string($data, 'bundleId'),
            Read::string($data, 'name'),
            Read::string($data, 'developer'),
            Read::string($data, 'url'),
            Read::string($data, 'iconUrl'),
            Read::float($data, 'price'),
            Read::string($data, 'currency'),
            Read::float($data, 'rating'),
            Read::int($data, 'ratingCount'),
            Read::string($data, 'version'),
            Read::strings($data, 'genres'),
            Read::strings($data, 'screenshots'),
        );
    }
}
