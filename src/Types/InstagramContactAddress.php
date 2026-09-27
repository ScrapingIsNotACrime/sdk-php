<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A profile's structured business address, from the contact endpoint. */
final readonly class InstagramContactAddress implements FromArray
{
    /** @internal */
    public function __construct(
        public string $streetAddress,
        public string $zipCode,
        public string $cityName,
        public string $regionName,
        public string $countryCode,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'street_address'),
            Read::string($data, 'zip_code'),
            Read::string($data, 'city_name'),
            Read::string($data, 'region_name'),
            Read::string($data, 'country_code'),
        );
    }
}
