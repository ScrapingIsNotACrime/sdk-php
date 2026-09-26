<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /appstore/search */
final readonly class AppstoreSearch implements FromArray
{
    /** @param list<AppstoreApp> $apps */
    public function __construct(
        public string $term,
        public string $country,
        public int $resultCount,
        public array $apps,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'term'),
            Read::string($data, 'country'),
            Read::int($data, 'resultCount'),
            Read::objects($data, 'apps', AppstoreApp::class),
        );
    }
}
