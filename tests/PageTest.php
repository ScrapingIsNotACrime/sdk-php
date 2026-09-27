<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests;

use Http\Mock\Client as MockClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ScrapingIsNotACrime\Config;
use ScrapingIsNotACrime\Exception\NotFoundException;
use ScrapingIsNotACrime\Http\PageSpec;
use ScrapingIsNotACrime\Http\Route;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\Tests\Support\Fake;
use ScrapingIsNotACrime\Tests\Support\Item;
use ScrapingIsNotACrime\Tests\Support\ItemPage;

final class PageTest extends TestCase
{
    /** @param list<string> $bodies */
    private static function client(array $bodies): MockClient
    {
        $client = new MockClient();
        foreach ($bodies as $body) {
            $client->addResponse(Fake::json(200, $body));
        }

        return $client;
    }

    /** @return list<string> */
    private static function requestUris(MockClient $client): array
    {
        return array_values(array_map(
            static fn(RequestInterface $r): string => (string) $r->getUri(),
            $client->getRequests(),
        ));
    }

    /**
     * @param list<array{string, string}> $query
     * @param 'cursor'|'numbered'         $kind
     */
    private static function spec(
        string $path = '/p',
        array $query = [],
        string $kind = 'cursor',
        ?string $cursor = null,
        int $page = 0,
        int $maxPage = 0,
    ): PageSpec {
        return new PageSpec($path, $query, $kind, 'items', $cursor, $page, $maxPage, ItemPage::class, 'items');
    }

    public function testCursorPagination(): void
    {
        $client = self::client([
            '{"data":{"items":[{"id":1},{"id":2}],"has_more":true,"next_cursor":"c2"}}',
            '{"data":{"items":[{"id":3}],"has_more":false,"next_cursor":""}}',
        ]);
        $core = Fake::core($client);
        /** @var Page<Item, ItemPage> $page */
        $page = $core->page(self::spec(query: Route::q('limit', 2)));

        self::assertCount(2, $page->items);
        self::assertTrue($page->hasMore);
        self::assertSame('c2', $page->nextCursor);
        self::assertNull($page->nextPage);

        $ids = [];
        foreach ($page as $item) {
            $ids[] = $item->id;
        }
        self::assertSame([1, 2, 3], $ids);

        $uris = self::requestUris($client);
        self::assertSame(Config::DEFAULT_BASE_URL . '/p?limit=2', $uris[0]);
        self::assertSame(Config::DEFAULT_BASE_URL . '/p?limit=2&cursor=c2', $uris[1]);
    }

    public function testEmptyCursorEndsSequence(): void
    {
        $client = self::client(['{"data":{"items":[{"id":1}],"has_more":true,"next_cursor":""}}']);
        $page = Fake::core($client)->page(self::spec());

        self::assertFalse($page->hasMore);
        self::assertNull($page->nextCursor);
        self::assertNull($page->next());
    }

    public function testNumberedPaginationUsesHasMore(): void
    {
        $client = self::client([
            '{"data":{"items":[{"id":1}],"has_more":true}}',
            '{"data":{"items":[{"id":2}],"has_more":false}}',
        ]);
        $core = Fake::core($client);
        /** @var Page<Item, ItemPage> $page */
        $page = $core->page(self::spec(path: '/n', query: Route::q('limit', 1), kind: 'numbered', page: 0));

        self::assertTrue($page->hasMore);
        self::assertSame(1, $page->nextPage);
        self::assertNull($page->nextCursor);

        $next = $page->next();
        self::assertNotNull($next);
        self::assertFalse($next->hasMore);
        self::assertNull($next->nextPage);
        self::assertSame(2, $next->items[0]->id);

        $uris = self::requestUris($client);
        self::assertSame(Config::DEFAULT_BASE_URL . '/n?limit=1&page=0', $uris[0]);
        self::assertSame(Config::DEFAULT_BASE_URL . '/n?limit=1&page=1', $uris[1]);
    }

    public function testNumberedMaxPageWithoutHasMore(): void
    {
        $client = self::client(['{"data":{"items":[{"id":1}]}}']);
        $page = Fake::core($client)->page(self::spec(path: '/r', kind: 'numbered', page: 10, maxPage: 10));
        self::assertFalse($page->hasMore);
        self::assertCount(1, $page->items);

        $client = self::client(['{"data":{"items":[{"id":1}]}}']);
        $page = Fake::core($client)->page(self::spec(path: '/r', kind: 'numbered', page: 3, maxPage: 10));
        self::assertTrue($page->hasMore);
        self::assertSame(4, $page->nextPage);
    }

    public function testEmptyItemsStopsEvenWithHasMore(): void
    {
        $client = self::client(['{"data":{"items":[],"has_more":true}}']);
        $page = Fake::core($client)->page(self::spec(path: '/n', kind: 'numbered', page: 1));

        self::assertFalse($page->hasMore);
        self::assertNull($page->nextPage);
        self::assertNull($page->next());
    }

    public function testEmptyItemsWithCursorForcesHasMoreFalse(): void
    {
        $client = self::client(['{"data":{"items":[],"has_more":true,"next_cursor":"c2"}}']);
        $page = Fake::core($client)->page(self::spec());

        self::assertFalse($page->hasMore);
        self::assertNull($page->nextCursor);
        self::assertNull($page->next());
    }

    public function testBreakStopsFetching(): void
    {
        $client = self::client(['{"data":{"items":[{"id":1},{"id":2}],"has_more":true,"next_cursor":"c2"}}']);
        $page = Fake::core($client)->page(self::spec());

        foreach ($page as $item) {
            break;
        }

        self::assertCount(1, $client->getRequests());
    }

    public function testFetchErrorDuringIterationPropagates(): void
    {
        $client = self::client(['{"data":{"items":[{"id":1}],"has_more":true,"next_cursor":"c2"}}']);
        $client->addResponse(Fake::json(404, '{"message":"gone"}'));
        $page = Fake::core($client)->page(self::spec());

        $this->expectException(NotFoundException::class);
        foreach ($page as $item) {
            // consume until the second page's fetch fails
        }
    }

    public function testIteratorToArrayKeepsEveryItemAcrossPages(): void
    {
        $client = self::client([
            '{"data":{"items":[{"id":1},{"id":2}],"has_more":true,"next_cursor":"c2"}}',
            '{"data":{"items":[{"id":3}],"has_more":false,"next_cursor":""}}',
        ]);
        /** @var Page<Item, ItemPage> $page */
        $page = Fake::core($client)->page(self::spec());

        $all = iterator_to_array($page);
        self::assertSame([1, 2, 3], array_map(static fn(Item $item): int => $item->id, $all));
        self::assertSame([0, 1, 2], array_keys($all));
    }
}
