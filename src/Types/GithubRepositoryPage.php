<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/** GET /github/profiles/{handle}/repositories */
final readonly class GithubRepositoryPage implements FromArray
{
    /**
     * @internal
     *
     * @param list<GithubRepository> $items
     */
    public function __construct(
        public array $items,
        /** Always null: GitHub's REST API does not report a count for this collection. */
        public ?int $total,
        public bool $hasMore,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        return new self(
            Read::objects($data, 'items', GithubRepository::class),
            Read::nullableInt($data, 'total'),
            Read::bool($data, 'has_more'),
        );
    }
}
