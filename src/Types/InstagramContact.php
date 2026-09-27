<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/profile/{username}/contact */
final readonly class InstagramContact implements FromArray
{
    /**
     * @internal
     *
     * @param list<InstagramFoundContact> $emailsFound
     * @param list<InstagramFoundContact> $phonesFound
     */
    public function __construct(
        public string $username,
        public string $fullName,
        public string $biography,
        public bool $isVerified,
        public bool $isBusiness,
        public string $category,
        public ?string $email,
        public ?string $phone,
        public string $externalUrl,
        /** Null for a profile with no contact information (per the endpoint's docs, all fields in that case are null). */
        public ?InstagramContactAddress $address,
        public array $emailsFound,
        public array $phonesFound,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'username'),
            Read::string($data, 'full_name'),
            Read::string($data, 'biography'),
            Read::bool($data, 'is_verified'),
            Read::bool($data, 'is_business'),
            Read::string($data, 'category'),
            Read::nullableString($data, 'email'),
            Read::nullableString($data, 'phone'),
            Read::string($data, 'external_url'),
            Read::object($data, 'address', InstagramContactAddress::class),
            Read::objects($data, 'emails_found', InstagramFoundContact::class),
            Read::objects($data, 'phones_found', InstagramFoundContact::class),
        );
    }
}
