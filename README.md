# ScrapingIsNotACrime PHP SDK

Official PHP SDK for the [ScrapingIsNotACrime](https://scrapingisnotacrime.com) public data API — typed access to Instagram, TikTok, YouTube, the App Store, GitHub, Hacker News, Bluesky, Twitch and Linktree.

## Install

```bash
composer require scrapingisnotacrime/sdk symfony/http-client
```

The SDK talks over any [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client. `symfony/http-client` above is a recommendation, not a hard requirement: with either Symfony HttpClient or Guzzle (`guzzlehttp/guzzle`) installed, the SDK builds its own client from whichever one it finds and configures its timeout and turns off redirects on it — but only for that client. Without either installed, the SDK falls back to whatever PSR-18 client [`php-http/discovery`](https://github.com/php-http/discovery) finds already in your project, and that client's own timeout and redirect behavior apply instead (see [Bring your own client](#bring-your-own-client) to configure one yourself). If no PSR-18 client exists at all, the constructor throws with a `composer require` hint.

Requires PHP 8.2+.

## Quick start

```php
use ScrapingIsNotACrime\Client;
use ScrapingIsNotACrime\Exception\NotFoundException;

$client = new Client(apiKey: 'sinac_...');

try {
    $profile = $client->instagram->profile('nasa');
    echo $profile->username, ' ', $profile->followers, PHP_EOL;
} catch (NotFoundException) {
    echo 'no such profile', PHP_EOL;
}
```

`new Client()` with no arguments also reads the API key from the `SCRAPINGISNOTACRIME_API_KEY` environment variable. Get a key at [scrapingisnotacrime.com/dashboard/api-keys](https://scrapingisnotacrime.com/dashboard/api-keys). Keys start with `sinac_`.

## Configuration

```php
$client = new Client(
    apiKey: 'sinac_...',
    baseUrl: 'https://api.scrapingisnotacrime.com/v1',
    timeout: 30.0,
    maxRetries: 2,
    httpClient: null,
    requestFactory: null,
);
```

| Argument | Default | Description |
|---|---|---|
| `apiKey` | `SCRAPINGISNOTACRIME_API_KEY` env var | Your API key (`sinac_…`). The constructor throws `\InvalidArgumentException` if none is set. |
| `baseUrl` | `https://api.scrapingisnotacrime.com/v1` | Should be the final HTTPS URL; http is accepted for local testing; redirects are not followed, so a base URL that itself redirects fails (see [Errors](#errors)) — following one would forward the `X-Api-Key` header to whatever host it points to. |
| `timeout` | `30.0` seconds | Per attempt, covering connect, headers and the whole body read. Applied when the SDK builds the client itself (Symfony HttpClient or Guzzle); ignored when you pass your own `httpClient`. |
| `maxRetries` | `2` | Extra attempts for 429, 502 and network errors. `0` disables retries. |
| `httpClient` | a client the SDK builds itself | Bring your own PSR-18 `Psr\Http\Client\ClientInterface`. It is used as is: set its timeout and disable redirects yourself. |
| `requestFactory` | discovered via `php-http/discovery` | Bring your own PSR-17 `Psr\Http\Message\RequestFactoryInterface`. |

## Bring your own client

Pass `httpClient` to use a client you configure yourself — for tests, proxies or connection pooling. The SDK never modifies it, so set its own timeout and turn off redirects (the SDK does this automatically only for the client it builds itself).

Guzzle:

```php
use GuzzleHttp\Client;
use ScrapingIsNotACrime\Client as ScrapingIsNotACrimeClient;

$client = new ScrapingIsNotACrimeClient(
    apiKey: 'sinac_...',
    httpClient: new Client([
        'timeout' => 30,
        'allow_redirects' => false,
        // 'proxy' => 'http://localhost:8080',
    ]),
);
```

Symfony HttpClient:

```php
use ScrapingIsNotACrime\Client as ScrapingIsNotACrimeClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

$client = new ScrapingIsNotACrimeClient(
    apiKey: 'sinac_...',
    httpClient: new Psr18Client(HttpClient::create([
        'timeout' => 30,
        'max_duration' => 30,
        'max_redirects' => 0,
        // 'proxy' => 'http://localhost:8080',
    ])),
);
```

## Methods

`Client` groups its methods under a property per platform: `$client->instagram`, `$client->tiktok`, `$client->youtube`, `$client->appstore`, `$client->github`, `$client->hackernews`, `$client->bluesky`, `$client->twitch`, `$client->linktree`. Every method returns the response envelope's `data`, decoded into a typed `Types\*` object; a method marked `Page` returns a `Page` instead (see [Pagination](#pagination)).

| Namespace | Method | Arguments | Route | Page |
|---|---|---|---|---|
| Instagram | `profile` | `username` | `/instagram/profile/{username}` | |
| Instagram | `contact` | `username` | `/instagram/profile/{username}/contact` | |
| Instagram | `latestPosts` | `username` | `/instagram/profile/{username}/timeline/latest` | |
| Instagram | `posts` | `username`, `count` 1-50 (default 12), `cursor` | `/instagram/profile/{username}/timeline` | Page |
| Instagram | `highlights` | `username` | `/instagram/profile/{username}/highlights` | |
| Instagram | `highlight` | `highlightId` | `/instagram/highlights/{highlightId}` | |
| Instagram | `mediaById` | `username`, `mediaId` | `/instagram/profile/{username}/media/{mediaId}` | |
| Instagram | `media` | `shortcode` | `/instagram/media/{shortcode}` | |
| Instagram | `download` | `shortcode` | `/instagram/media/{shortcode}/download` | |
| Instagram | `shortcodeToId` | `shortcode` | `/instagram/media/{shortcode}/id` | |
| Instagram | `idToShortcode` | `mediaId` | `/instagram/media/id/{mediaId}` | |
| Instagram | `reel` | `shortcode` | `/instagram/reels/{shortcode}` | |
| TikTok | `profile` | `username` | `/tiktok/profile/{username}` | |
| TikTok | `video` | `videoId` | `/tiktok/video/{videoId}` | |
| YouTube | `videos` | `handle` | `/youtube/channel/{handle}/videos` | |
| App Store | `search` | `term`, `country` (default `"us"`), `limit` 1-200 (default 10) | `/appstore/search` | |
| App Store | `reviews` | `appId`, `country` (default `"us"`), `page` 1-10 (default 1) | `/appstore/reviews` | Page |
| GitHub | `profile` | `handle` | `/github/profiles/{handle}` | |
| GitHub | `followers` | `handle`, `limit` 1-100 (default 30), `page` 1-based (default 1) | `/github/profiles/{handle}/followers` | Page |
| GitHub | `following` | `handle`, same params as `followers` | `/github/profiles/{handle}/following` | Page |
| GitHub | `repositories` | `handle`, same params as `followers` | `/github/profiles/{handle}/repositories` | Page |
| GitHub | `searchRepositories` | `q` (GitHub search syntax), same paging params as `followers` | `/github/repositories` | Page |
| GitHub | `trending` | `since` (default `daily`), `language`, `limit` 1-100 (default 30) | `/github/trending/repositories` | |
| Hacker News | `feed` | `feed`, `limit` 1-50 (default 20), `page` 0-based (default 0) | `/hackernews/feeds/{feed}` | Page |
| Hacker News | `item` | `id` | `/hackernews/items/{id}` | |
| Hacker News | `search` | `q`, same params as `feed` | `/hackernews/search` | Page |
| Hacker News | `user` | `username` | `/hackernews/users/{username}` | |
| Hacker News | `submissions` | `username`, same params as `feed` | `/hackernews/users/{username}/submissions` | Page |
| Hacker News | `comments` | `username`, same params as `feed` | `/hackernews/users/{username}/comments` | Page |
| Bluesky | `profile` | `handle` (full handle, including the domain) | `/bluesky/profiles/{handle}` | |
| Bluesky | `posts` | `handle`, `limit` 1-100 (default 25), `cursor` | `/bluesky/profiles/{handle}/posts` | Page |
| Twitch | `profile` | `handle` | `/twitch/profiles/{handle}` | |
| Twitch | `videos` | `handle`, `limit` 1-100 (default 20) | `/twitch/profiles/{handle}/videos` | |
| Linktree | `profile` | `handle` | `/linktree/profiles/{handle}` | |

`since` (`trending`'s argument) accepts a `GithubTrendingSince` case (`GithubTrendingSince::Daily`/`Weekly`/`Monthly`) or its string value; `feed` (`feed`'s argument) accepts a `HackernewsFeed` case (`HackernewsFeed::Top`/`New`/`Best`/`Ask`/`Show`/`Job`) or its string value — an unrecognized string throws `\InvalidArgumentException`. Every optional argument defaults to `null`, meaning "use the API's default". Path arguments (`username`, `handle`, `shortcode`, …) are validated before any request: an empty string, `"."` or `".."` throws `\InvalidArgumentException`, and the value is percent-encoded the way JavaScript's `encodeURIComponent` would encode it.

## Pagination

Methods marked `Page` return a `ScrapingIsNotACrime\Page`:

```php
final class Page implements \IteratorAggregate
{
    public readonly array $items;      // this page's items, already decoded
    public readonly bool $hasMore;
    public readonly ?string $nextCursor; // cursor endpoints; null when there is none
    public readonly ?int $nextPage;      // page-number endpoints; null when there is none
    public readonly object $data;        // the untouched response of this page

    public function next(): ?self; // fetches the following page; null when there is none
    public function getIterator(): \Generator; // yields items across pages, lazily
}
```

`hasMore` is true exactly when `next()` will fetch another page (an empty final page, or an announced next page/cursor with no items, both leave it `false`).

Walk pages one at a time with `next()`. **Each page fetched is one billed request**, so bound the loop:

```php
$page = $client->bluesky->posts('bsky.app', limit: 25);
$fetched = 0;
while ($page !== null && $fetched < 3) {
    foreach ($page->items as $post) {
        echo $post->text, PHP_EOL;
    }
    $page = $page->next();
    $fetched++;
}
```

Or iterate every item with `foreach`, which fetches later pages lazily. **Iterating fetches every remaining page; each page is one billed request**, so bound the loop:

```php
$page = $client->github->followers('torvalds', limit: 100);
$count = 0;
foreach ($page as $user) {
    echo $user->username, PHP_EOL;
    if (++$count >= 250) {
        break; // stop early; no further pages are fetched
    }
}
```

`$page->data` gives you the untouched response of the current page, so fields such as a total count stay reachable.

## Errors

Every failure the SDK raises is an exception under `ScrapingIsNotACrime\Exception`, all extending `ScrapingIsNotACrimeException` (itself a `\RuntimeException`) with `->status` (the HTTP status, or `null` for network errors) and `->requestId` (the `X-Request-Id` header, when the API sends one). An invalid argument — a bad path segment, an unrecognized enum string, an out-of-range option — throws `\InvalidArgumentException` instead, before any request is made.

| Class | Status | Retried |
|---|---|---|
| `BadRequestException` | 400 | no |
| `AuthenticationException` | 401 | no |
| `QuotaExceededException` | 402 | no |
| `NotFoundException` | 404 | no |
| `RateLimitException` | 429 | yes |
| `UpstreamException` | 502 | yes |
| `ConnectionException` | network failure or per-attempt timeout; `->getPrevious()` is the underlying client's exception | yes |
| `ApiException` | any other status, a 2xx without the JSON envelope, a redirect, or data of an unexpected shape | no |

```php
use ScrapingIsNotACrime\Exception\NotFoundException;
use ScrapingIsNotACrime\Exception\QuotaExceededException;
use ScrapingIsNotACrime\Exception\RateLimitException;
use ScrapingIsNotACrime\Exception\ScrapingIsNotACrimeException;

try {
    $profile = $client->tiktok->profile('this-user-does-not-exist-123');
    echo $profile->username, PHP_EOL;
} catch (NotFoundException) {
    echo 'no such profile', PHP_EOL;
} catch (QuotaExceededException $e) {
    throw $e; // includes the pricing link in the message
} catch (RateLimitException) {
    echo 'TikTok is rate limiting; already retried, try again later', PHP_EOL;
} catch (ScrapingIsNotACrimeException $e) {
    throw $e;
}
```

## Retries

`RateLimitException` (429), `UpstreamException` (502) and `ConnectionException` (network failure or per-attempt timeout) are retried automatically, up to `maxRetries` additional attempts (default 2). These failures don't consume credits, so retrying them costs you nothing.

The delay before each retry uses the `Retry-After` header when the API sends one (seconds or an HTTP date); otherwise it's exponential backoff with full jitter, starting around 500 ms and doubling per attempt. Every wait is capped at 10 seconds. Set `maxRetries: 0` to disable retries entirely.

## Releases and changelog

Every merge to `main` is released automatically: the version comes from the commit messages since the last release, following [Conventional Commits](https://www.conventionalcommits.org/). The pipeline tags `vX.Y.Z` and publishes the GitHub Release with the notes — the tag is the release, and [Packagist](https://packagist.org/packages/scrapingisnotacrime/sdk)'s GitHub hook picks up the new version, no separate publish step. The changelog is the [Releases page](https://github.com/ScrapingIsNotACrime/sdk-php/releases).

Merge PRs with a merge commit or rebase so each Conventional Commit is analysed; if you squash, the PR title must be a Conventional Commit (e.g. `feat: ...`).

## Links

- Docs: https://scrapingisnotacrime.com/docs
- Pricing: https://scrapingisnotacrime.com/#pricing
- Releases: https://github.com/ScrapingIsNotACrime/sdk-php/releases
- Go SDK: https://github.com/ScrapingIsNotACrime/sdk-go
- Python SDK: https://github.com/ScrapingIsNotACrime/sdk-python
- Node.js SDK: https://github.com/ScrapingIsNotACrime/sdk-nodejs
- MCP server: https://github.com/ScrapingIsNotACrime/mcp
- License: [MIT](./LICENSE)
