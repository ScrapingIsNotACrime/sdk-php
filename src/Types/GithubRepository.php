<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** A repository, as returned by the profile repositories list, search, and trending endpoints. */
final readonly class GithubRepository implements FromArray
{
    /** @param list<string> $topics */
    public function __construct(
        public string $name,
        public string $fullName,
        /** Null when the repository has no description. */
        public ?string $description,
        public int $stars,
        public int $forks,
        /** Primary language; null when GitHub has not detected one. */
        public ?string $language,
        public array $topics,
        public bool $isFork,
        public bool $isArchived,
        public string $createdAt,
        public string $updatedAt,
        public string $url,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'name'),
            Read::string($data, 'full_name'),
            Read::nullableString($data, 'description'),
            Read::int($data, 'stars'),
            Read::int($data, 'forks'),
            Read::nullableString($data, 'language'),
            Read::strings($data, 'topics'),
            Read::bool($data, 'is_fork'),
            Read::bool($data, 'is_archived'),
            Read::string($data, 'created_at'),
            Read::string($data, 'updated_at'),
            Read::string($data, 'url'),
        );
    }
}
