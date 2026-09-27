<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** One customer review of an app. */
final readonly class AppstoreReview implements FromArray
{
    /** @internal */
    public function __construct(
        public string $id,
        public string $author,
        public float $rating,
        public string $title,
        public string $content,
        public string $version,
        public string $updatedAt,
        public int $voteCount,
        public int $voteSum,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::string($data, 'id'),
            Read::string($data, 'author'),
            Read::float($data, 'rating'),
            Read::string($data, 'title'),
            Read::string($data, 'content'),
            Read::string($data, 'version'),
            Read::string($data, 'updatedAt'),
            Read::int($data, 'voteCount'),
            Read::int($data, 'voteSum'),
        );
    }
}
