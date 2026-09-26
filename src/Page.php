<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

/**
 * One page of results. Iterating yields every item from this page onward,
 * fetching later pages lazily; each page fetched is one billed request.
 *
 * @template T of object
 * @template R of object
 * @implements \IteratorAggregate<int, T>
 */
final class Page implements \IteratorAggregate
{
    /**
     * @param list<T> $items
     * @param R $data
     * @param (\Closure(): Page<T, R>)|null $fetchNext null when there is no next page
     */
    public function __construct(
        public readonly array $items,
        public readonly bool $hasMore,
        public readonly ?string $nextCursor,
        public readonly ?int $nextPage,
        public readonly object $data,
        private readonly ?\Closure $fetchNext,
    ) {}

    /** @return Page<T, R>|null the following page, or null when there is none */
    public function next(): ?self
    {
        return $this->fetchNext === null ? null : ($this->fetchNext)();
    }

    /** @return \Generator<int, T> */
    public function getIterator(): \Generator
    {
        $index = 0;
        for ($page = $this; $page !== null; $page = $page->next()) {
            foreach ($page->items as $item) {
                yield $index++ => $item;
            }
        }
    }
}
