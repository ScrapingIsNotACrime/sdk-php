<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use ScrapingIsNotACrime\Config;
use ScrapingIsNotACrime\Exception\ApiException;
use ScrapingIsNotACrime\Exception\ConnectionException;
use ScrapingIsNotACrime\Exception\ScrapingIsNotACrimeException;
use ScrapingIsNotACrime\Page;
use ScrapingIsNotACrime\RetryDelay;
use ScrapingIsNotACrime\Version;

/** @internal */
final class HttpCore
{
    private readonly \Closure $sleep;
    private readonly \Closure $random;
    /** Retry-After of the last failed attempt; read by the retry loop. */
    private ?string $lastRetryAfter = null;

    public function __construct(
        private readonly Config $config,
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requests,
        ?\Closure $sleep = null,
        ?\Closure $random = null,
    ) {
        $this->sleep = $sleep ?? static function (float $seconds): void {
            usleep((int) round($seconds * 1_000_000));
        };
        $this->random = $random ?? static fn(): float => mt_rand() / mt_getrandmax();
    }

    /** The envelope's data, decoded. */
    public function get(Route $route): mixed
    {
        $url = $route->url($this->config->baseUrl);
        for ($attempt = 0; ; $attempt++) {
            try {
                return $this->attempt($url);
            } catch (ScrapingIsNotACrimeException $e) {
                if ($attempt >= $this->config->maxRetries || !$e->isRetryable()) {
                    throw $e;
                }
                ($this->sleep)(RetryDelay::seconds($attempt, $this->lastRetryAfter, $this->random));
            }
        }
    }

    /** @return array<string, mixed> */
    public function getObject(Route $route): array
    {
        return self::object($this->get($route));
    }

    /** @return Page<object, object> */
    public function page(PageSpec $spec): Page
    {
        $raw = self::object($this->get($spec->route()));
        $dataClass = $spec->dataClass;
        /** @var object $data */
        $data = $dataClass::fromArray($raw);
        $items = $data->{$spec->itemsProperty};
        /** @var list<object> $items the typed page's items property (a list<Types\*> on every real page class), read dynamically */
        $items = is_array($items) ? array_values($items) : [];
        $hasMore = ($raw['has_more'] ?? false) === true;

        if ($spec->kind === 'cursor') {
            $cursor = is_string($raw['next_cursor'] ?? null) ? $raw['next_cursor'] : '';
            $more = $hasMore && $cursor !== '' && $items !== [];
            $next = $more ? $spec->advance($cursor, null) : null;
        } else {
            $more = ($spec->maxPage > 0 ? $spec->page < $spec->maxPage : $hasMore) && $items !== [];
            $next = $more ? $spec->advance(null, $spec->page + 1) : null;
        }

        return new Page(
            $items,
            $next !== null,
            $next?->cursor,
            $next !== null && $spec->kind === 'numbered' ? $next->page : null,
            $data,
            $next === null ? null : fn(): Page => $this->page($next),
        );
    }

    /** @return array<string, mixed> */
    public static function object(mixed $data): array
    {
        if (!is_array($data) || ($data !== [] && array_is_list($data))) {
            throw new ApiException('unexpected response data: expected a JSON object, got ' . get_debug_type($data));
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    private function attempt(string $url): mixed
    {
        $this->lastRetryAfter = null;
        $request = $this->requests->createRequest('GET', $url)
            ->withHeader('X-Api-Key', $this->config->apiKey)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', Version::userAgent());
        try {
            $response = $this->client->sendRequest($request);
            $body = (string) $response->getBody();
        } catch (ClientExceptionInterface $e) {
            throw new ConnectionException('network error: ' . $e->getMessage(), null, null, $e);
        } catch (\RuntimeException $e) {
            // Reading a streamed body can fail after headers arrived (timeout, reset).
            throw new ConnectionException('network error: ' . $e->getMessage(), null, null, $e);
        }

        return $this->interpret($response, $body);
    }

    private function interpret(ResponseInterface $response, string $body): mixed
    {
        $status = $response->getStatusCode();
        $requestId = $response->getHeaderLine('X-Request-Id') ?: null;
        try {
            $decoded = $body === '' ? null : json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $decoded = null;
        }
        $envelope = is_array($decoded) && ($decoded === [] || !array_is_list($decoded)) ? $decoded : null;

        if ($status >= 200 && $status < 300) {
            if ($envelope !== null && array_key_exists('data', $envelope)) {
                return $envelope['data'];
            }
            throw new ApiException('unexpected response body: ' . self::snippet($body), $status, $requestId);
        }

        $this->lastRetryAfter = $response->getHeaderLine('Retry-After') ?: null;
        if ($status >= 300 && $status < 400) {
            $location = $response->getHeaderLine('Location');
            $message = $location !== '' ? "redirect to $location not followed" : 'redirect not followed';
            throw new ApiException($message, $status, $requestId);
        }

        $message = $envelope !== null && is_string($envelope['message'] ?? null) ? $envelope['message'] : self::snippet($body);
        throw ScrapingIsNotACrimeException::fromStatus($status, $message, $requestId);
    }

    private static function snippet(string $body): string
    {
        $unicode = preg_replace('/\s+/u', ' ', $body);
        if ($unicode === null) {
            // Invalid UTF-8: fall back to byte-oriented whitespace collapsing
            // and truncate by raw byte length instead of by codepoint.
            $flat = trim((string) preg_replace('/\s+/', ' ', $body));
            if ($flat === '') {
                return 'empty response body';
            }

            return strlen($flat) > 200 ? substr($flat, 0, 200) . '…' : $flat;
        }

        $flat = trim($unicode);
        if ($flat === '') {
            return 'empty response body';
        }
        if (preg_match('/^(.{200}).+/us', $flat, $m) === 1) {
            return $m[1] . '…';
        }

        return $flat;
    }
}
