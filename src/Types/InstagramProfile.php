<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /instagram/profile/{username} */
final readonly class InstagramProfile implements FromArray
{
    /** @internal */
    public function __construct(
        public string $id,
        public string $fbid,
        public string $username,
        public string $fullName,
        public string $bio,
        /** @var list<string> */
        public array $bioLinks,
        public int $followers,
        public int $following,
        public int $medias,
        public int $highlightReelCount,
        public string $profilePic,
        public bool $hasArEffects,
        public bool $hasClips,
        public bool $hasGuides,
        public bool $hasChannel,
        public bool $hasBlockedViewer,
        public bool $isBusinessAccount,
        /** Null in every observed example; no evidence of its populated shape. */
        public mixed $businessAddressJson,
        public string $businessContactMethod,
        public ?string $businessEmail,
        public ?string $businessPhoneNumber,
        public string $businessCategoryName,
        public bool $isProfessionalAccount,
        public string $categoryName,
        public bool $isPrivate,
        public bool $isVerified,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'fbid'),
            Read::string($data, 'username'),
            Read::string($data, 'full_name'),
            Read::string($data, 'bio'),
            Read::strings($data, 'bio_links'),
            Read::int($data, 'followers'),
            Read::int($data, 'following'),
            Read::int($data, 'medias'),
            Read::int($data, 'highlight_reel_count'),
            Read::string($data, 'profile_pic'),
            Read::bool($data, 'has_ar_effects'),
            Read::bool($data, 'has_clips'),
            Read::bool($data, 'has_guides'),
            Read::bool($data, 'has_channel'),
            Read::bool($data, 'has_blocked_viewer'),
            Read::bool($data, 'is_business_account'),
            Read::mixed($data, 'business_address_json'),
            Read::string($data, 'business_contact_method'),
            Read::nullableString($data, 'business_email'),
            Read::nullableString($data, 'business_phone_number'),
            Read::string($data, 'business_category_name'),
            Read::bool($data, 'is_professional_account'),
            Read::string($data, 'category_name'),
            Read::bool($data, 'is_private'),
            Read::bool($data, 'is_verified'),
        );
    }
}
