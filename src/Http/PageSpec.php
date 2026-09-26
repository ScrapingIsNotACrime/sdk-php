<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Http;

/**
 * Describes one paginated endpoint: enough to build the request for the
 * current page and to advance to the next one.
 *
 * @internal
 */
final readonly class PageSpec
{
    /**
     * @param list<array{string, string}> $query query pairs sent on every page, before "cursor"/"page"
     * @param 'cursor'|'numbered' $kind
     * @param ?string $cursor cursorPages: null is the first page
     * @param int $page numberedPages: the page to request (always sent)
     * @param int $maxPage >0 marks numbered endpoints without a has_more flag (App Store
     *                     reviews): more while the page is non-empty and below this cap; 0 = none
     * @param class-string $dataClass
     */
    public function __construct(
        public string $path,
        public array $query,
        public string $kind,
        public string $itemsKey,
        public ?string $cursor,
        public int $page,
        public int $maxPage,
        public string $dataClass,
        public string $itemsProperty,
    ) {}

    public function route(): Route
    {
        if ($this->kind === 'cursor') {
            return new Route($this->path, [...$this->query, ...Route::q('cursor', $this->cursor)]);
        }

        return new Route($this->path, [...$this->query, ['page', (string) $this->page]]);
    }

    /** Returns a copy advanced to the next cursor (cursorPages) or page (numberedPages). */
    public function advance(?string $cursor, ?int $page): self
    {
        return new self(
            $this->path,
            $this->query,
            $this->kind,
            $this->itemsKey,
            $cursor,
            $page ?? $this->page,
            $this->maxPage,
            $this->dataClass,
            $this->itemsProperty,
        );
    }
}
