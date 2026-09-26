<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /hackernews/users/{username} */
final readonly class HackernewsUser implements FromArray
{
    public function __construct(
        public string $username,
        public int $karma,
        /** Profile bio as HTML; null when the user has not written one. */
        public ?string $about,
        public string $createdAt,
        public int $submissionCount,
        public string $hnUrl,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'username'),
            Read::int($data, 'karma'),
            Read::nullableString($data, 'about'),
            Read::string($data, 'created_at'),
            Read::int($data, 'submission_count'),
            Read::string($data, 'hn_url'),
        );
    }
}
