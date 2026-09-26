<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Exception;

/** 429 — the source platform rate-limited the request (retried; not charged). */
final class RateLimitException extends ScrapingIsNotACrimeException {}
