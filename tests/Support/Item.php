<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Support;

final readonly class Item
{
    public function __construct(public int $id) {}

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(is_int($d['id'] ?? null) ? $d['id'] : 0);
    }
}
