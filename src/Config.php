<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

/** @internal */
final readonly class Config
{
    public const DEFAULT_BASE_URL = 'https://api.scrapingisnotacrime.com/v1';
    public const API_KEY_ENV = 'SCRAPINGISNOTACRIME_API_KEY';
    public const PRICING_URL = 'https://scrapingisnotacrime.com/#pricing';

    private function __construct(
        public string $apiKey,
        public string $baseUrl,
        public float $timeout,
        public int $maxRetries,
    ) {}

    /** @param array<string, string>|null $env */
    public static function resolve(?string $apiKey, ?string $baseUrl, float $timeout, int $maxRetries, ?array $env = null): self
    {
        $key = trim($apiKey ?? '');
        if ($key === '') {
            $fromEnv = $env === null ? getenv(self::API_KEY_ENV) : ($env[self::API_KEY_ENV] ?? false);
            $key = trim(is_string($fromEnv) ? $fromEnv : '');
        }
        if ($key === '') {
            throw new \InvalidArgumentException(sprintf(
                'Missing API key: pass apiKey to the Client or set the %s environment variable.',
                self::API_KEY_ENV,
            ));
        }
        if (!is_finite($timeout) || $timeout <= 0) {
            throw new \InvalidArgumentException('timeout must be a positive number of seconds.');
        }
        if ($maxRetries < 0) {
            throw new \InvalidArgumentException('maxRetries must not be negative.');
        }
        $url = rtrim($baseUrl ?? self::DEFAULT_BASE_URL, '/');
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || ($parts['host'] ?? '') === '') {
            throw new \InvalidArgumentException(sprintf('Invalid base URL "%s".', $url));
        }

        return new self($key, $url, $timeout, $maxRetries);
    }
}
