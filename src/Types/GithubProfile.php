<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /github/profiles/{handle} */
final readonly class GithubProfile implements FromArray
{
    /** @internal */
    public function __construct(
        public string $username,
        public int $id,
        /** Null when the user has not set a display name. */
        public ?string $name,
        /** Null when the user has not set a bio. */
        public ?string $bio,
        public ?string $company,
        public ?string $location,
        /** Website URL; empty or null when the user has not set one. */
        public ?string $blog,
        public int $publicRepos,
        public int $followers,
        public int $following,
        public string $avatar,
        public string $createdAt,
        public string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'username'),
            Read::int($data, 'id'),
            Read::nullableString($data, 'name'),
            Read::nullableString($data, 'bio'),
            Read::nullableString($data, 'company'),
            Read::nullableString($data, 'location'),
            Read::nullableString($data, 'blog'),
            Read::int($data, 'public_repos'),
            Read::int($data, 'followers'),
            Read::int($data, 'following'),
            Read::string($data, 'avatar'),
            Read::string($data, 'created_at'),
            Read::string($data, 'url'),
        );
    }
}
