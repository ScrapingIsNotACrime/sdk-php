<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /github/repositories */
final readonly class GithubRepositorySearchPage implements FromArray
{
    /** @param list<GithubRepository> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public bool $hasMore,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::objects($data, 'items', GithubRepository::class),
            Read::int($data, 'total'),
            Read::int($data, 'page'),
            Read::bool($data, 'has_more'),
        );
    }
}
