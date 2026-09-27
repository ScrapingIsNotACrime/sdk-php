<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /bluesky/profiles/{handle} */
final readonly class BlueskyProfile implements FromArray
{
    /** @internal */
    public function __construct(
        public string $did,
        public string $handle,
        public string $displayName,
        /** Null when the profile has no bio. */
        public ?string $description,
        /** Null when the profile has no avatar set. */
        public ?string $avatar,
        /** Banner image URL; null when the profile has none set. */
        public ?string $banner,
        public int $followers,
        public int $following,
        public int $posts,
        public string $createdAt,
        public string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'did'),
            Read::string($data, 'handle'),
            Read::string($data, 'display_name'),
            Read::nullableString($data, 'description'),
            Read::nullableString($data, 'avatar'),
            Read::nullableString($data, 'banner'),
            Read::int($data, 'followers'),
            Read::int($data, 'following'),
            Read::int($data, 'posts'),
            Read::string($data, 'created_at'),
            Read::string($data, 'url'),
        );
    }
}
