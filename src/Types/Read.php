<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Types;

/**
 * Tolerant readers used by fromArray(): a missing key or a value of an
 * unexpected type becomes the type's default instead of failing the call.
 *
 * @internal
 */
final class Read
{
    /** @param array<string, mixed> $d */
    public static function string(array $d, string $key): string
    {
        return is_string($d[$key] ?? null) ? $d[$key] : '';
    }

    /** @param array<string, mixed> $d */
    public static function nullableString(array $d, string $key): ?string
    {
        return is_string($d[$key] ?? null) ? $d[$key] : null;
    }

    /** @param array<string, mixed> $d */
    public static function int(array $d, string $key): int
    {
        return self::nullableInt($d, $key) ?? 0;
    }

    /** @param array<string, mixed> $d */
    public static function nullableInt(array $d, string $key): ?int
    {
        $v = $d[$key] ?? null;
        if (is_int($v)) {
            return $v;
        }

        return is_float($v) && floor($v) === $v && abs($v) <= PHP_INT_MAX ? (int) $v : null;
    }

    /** @param array<string, mixed> $d */
    public static function float(array $d, string $key): float
    {
        return self::nullableFloat($d, $key) ?? 0.0;
    }

    /** @param array<string, mixed> $d */
    public static function nullableFloat(array $d, string $key): ?float
    {
        $v = $d[$key] ?? null;

        return is_int($v) || is_float($v) ? (float) $v : null;
    }

    /** @param array<string, mixed> $d */
    public static function bool(array $d, string $key): bool
    {
        return ($d[$key] ?? null) === true;
    }

    /** @param array<string, mixed> $d */
    public static function nullableBool(array $d, string $key): ?bool
    {
        return is_bool($d[$key] ?? null) ? $d[$key] : null;
    }

    /** @param array<string, mixed> $d */
    public static function mixed(array $d, string $key): mixed
    {
        return $d[$key] ?? null;
    }

    /**
     * @param array<string, mixed> $d
     * @return list<mixed>
     */
    public static function array(array $d, string $key): array
    {
        $v = $d[$key] ?? null;

        return is_array($v) && array_is_list($v) ? $v : [];
    }

    /**
     * @param array<string, mixed> $d
     * @return list<string>
     */
    public static function strings(array $d, string $key): array
    {
        return array_values(array_filter(self::array($d, $key), 'is_string'));
    }

    /**
     * @template C of FromArray
     * @param array<string, mixed> $d
     * @param class-string<C> $class
     * @return list<C>
     */
    public static function objects(array $d, string $key, string $class): array
    {
        $out = [];
        foreach (self::array($d, $key) as $item) {
            if (is_array($item) && ($item === [] || !array_is_list($item))) {
                /** @var array<string, mixed> $item */
                $out[] = $class::fromArray($item);
            }
        }

        return $out;
    }

    /**
     * @template C of FromArray
     * @param array<string, mixed> $d
     * @param class-string<C> $class
     * @return C|null
     */
    public static function object(array $d, string $key, string $class): ?object
    {
        $v = $d[$key] ?? null;
        if (!is_array($v) || ($v !== [] && array_is_list($v))) {
            return null;
        }

        /** @var array<string, mixed> $v */
        return $class::fromArray($v);
    }
}
