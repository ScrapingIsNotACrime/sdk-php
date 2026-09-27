<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Http;

/** @internal */
final readonly class Route
{
    /** @param list<array{string, string}> $query */
    public function __construct(public string $path, public array $query = []) {}

    /** Encodes one path segment like JavaScript's encodeURIComponent; rejects values that would drop or climb a level. */
    public static function segment(string $value): string
    {
        if ($value === '' || $value === '.' || $value === '..') {
            throw new \InvalidArgumentException(sprintf('Invalid path segment "%s".', $value));
        }

        return strtr(rawurlencode($value), ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')']);
    }

    public static function path(string $format, string ...$values): string
    {
        return vsprintf($format, array_map(self::segment(...), $values));
    }

    /** @return list<array{string, string}> a query pair, or nothing when $value is null */
    public static function q(string $key, string|int|null $value): array
    {
        return $value === null ? [] : [[$key, (string) $value]];
    }

    /** @param list<array{string, string}> ...$params */
    public function with(array ...$params): self
    {
        return new self($this->path, array_merge($this->query, ...$params));
    }

    public function url(string $baseUrl): string
    {
        if ($this->query === []) {
            return $baseUrl . $this->path;
        }
        $parts = array_map(static fn(array $p): string => urlencode($p[0]) . '=' . urlencode($p[1]), $this->query);

        return $baseUrl . $this->path . '?' . implode('&', $parts);
    }
}
