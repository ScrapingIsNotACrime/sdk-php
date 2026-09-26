<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Support;

final readonly class ItemPage
{
    /** @param list<array<string, mixed>> $items */
    public function __construct(public array $items) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $items = $data['items'] ?? [];

        // @phpstan-ignore argument.type (array_filter('is_array') keeps a broader array-key type than declared)
        return new self(is_array($items) ? array_values(array_filter($items, 'is_array')) : []);
    }
}
