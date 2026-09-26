<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime;

/** @internal */
final class RetryDelay
{
    public const MAX = 10.0;
    private const BASE = 0.5;

    /** Seconds to wait before retry number $attempt (0 for the first retry). */
    public static function seconds(int $attempt, ?string $retryAfter, ?callable $random = null, ?int $now = null): float
    {
        $fromHeader = self::parseRetryAfter($retryAfter, $now ?? time());
        if ($fromHeader !== null) {
            return min($fromHeader, self::MAX);
        }
        $random ??= static fn(): float => mt_rand() / mt_getrandmax();
        $value = $random();

        return min((is_float($value) || is_int($value) ? (float) $value : 0.0) * self::BASE * 2 ** $attempt, self::MAX);
    }

    private static function parseRetryAfter(?string $value, int $now): ?float
    {
        $text = trim($value ?? '');
        if ($text === '') {
            return null;
        }
        if (preg_match('/^\d+(\.\d+)?$/', $text) === 1) {
            return min((float) $text, self::MAX);
        }
        $moment = \DateTimeImmutable::createFromFormat('D, d M Y H:i:s \G\M\T', $text, new \DateTimeZone('UTC'));
        if ($moment === false) {
            return null;
        }

        return (float) max($moment->getTimestamp() - $now, 0);
    }
}
