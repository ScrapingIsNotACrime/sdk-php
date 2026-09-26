<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Support;

final readonly class ItemPage
{
    /** @param list<Item> $items */
    public function __construct(public array $items) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $items = $data['items'] ?? [];

        return new self(is_array($items) ? array_map(
            static fn(mixed $item): Item => Item::fromArray(is_array($item) ? $item : []),
            array_values($items),
        ) : []);
    }
}
