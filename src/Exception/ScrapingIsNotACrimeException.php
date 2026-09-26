<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Exception;

use ScrapingIsNotACrime\Config;

/** Base class of every exception the API calls throw. */
class ScrapingIsNotACrimeException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?string $requestId = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $status ?? 0, $previous);
    }

    public static function fromStatus(int $status, string $message, ?string $requestId): self
    {
        return match ($status) {
            400 => new BadRequestException($message, $status, $requestId),
            401 => new AuthenticationException($message, $status, $requestId),
            402 => new QuotaExceededException($message . ' — see ' . Config::PRICING_URL, $status, $requestId),
            404 => new NotFoundException($message, $status, $requestId),
            429 => new RateLimitException($message, $status, $requestId),
            502 => new UpstreamException($message, $status, $requestId),
            default => new ApiException($message, $status, $requestId),
        };
    }

    /** 429 and 502 don't consume credits, so retrying them costs the customer nothing. */
    public function isRetryable(): bool
    {
        return $this instanceof RateLimitException || $this instanceof UpstreamException || $this instanceof ConnectionException;
    }
}
