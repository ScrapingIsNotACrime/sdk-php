<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/**
 * Implemented by every response type. Bounds Read::objects()/Read::object()'s
 * generic parameter so the tolerant reader can call fromArray() on it without
 * an unconstrained "object" template, which PHPStan cannot verify.
 *
 * @internal
 */
interface FromArray
{
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static;
}
