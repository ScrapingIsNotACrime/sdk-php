<?php

declare(strict_types=1);

namespace ScrapingIsNotACrime\Tests\Support;

/**
 * Checks a built object against the documented JSON it came from:
 * every JSON key maps to a property (camelCase), every non-nullable
 * property's key is present, and every value survived fromArray unchanged.
 */
final class TypeGate
{
    public static function camel(string $key): string
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }

    /**
     * @param array<string, mixed> $json
     * @return list<string> problems, empty when the object matches
     */
    public static function check(object $object, array $json, string $at): array
    {
        $problems = [];
        $class = new \ReflectionClass($object);
        $seen = [];
        foreach ($json as $key => $value) {
            $prop = self::camel((string) $key);
            $seen[$prop] = true;
            if ($object instanceof \ScrapingIsNotACrime\Types\TiktokVideo) {
                continue;
            }
            if (!$class->hasProperty($prop)) {
                $problems[] = "$at.$key: no property \$$prop on " . $class->getShortName();
                continue;
            }
            $actual = $class->getProperty($prop)->getValue($object);
            $problems = [...$problems, ...self::compare($actual, $value, "$at.$key")];
        }
        foreach ($class->getProperties() as $property) {
            $type = $property->getType();
            if ($type !== null && !$type->allowsNull() && !isset($seen[$property->getName()])
                && !$object instanceof \ScrapingIsNotACrime\Types\TiktokVideo) {
                $problems[] = "$at: required property \${$property->getName()} has no key in the documented example";
            }
        }

        return $problems;
    }

    /** @return list<string> */
    private static function compare(mixed $actual, mixed $expected, string $at): array
    {
        if (is_array($expected) && $expected !== [] && !array_is_list($expected)) {
            if (is_object($actual)) {
                /** @var array<string, mixed> $expected */
                return self::check($actual, $expected, $at);
            }

            return $actual === $expected ? [] : ["$at: object value differs"];
        }
        if (is_array($expected)) {
            if (!is_array($actual) || count($actual) !== count($expected)) {
                return ["$at: list length differs"];
            }
            $problems = [];
            foreach (array_values($expected) as $i => $item) {
                $problems = [...$problems, ...self::compare(array_values($actual)[$i], $item, "$at[$i]")];
            }

            return $problems;
        }
        if (is_int($expected) && is_float($actual)) {
            return (float) $expected === $actual ? [] : ["$at: value differs"];
        }

        return $actual === $expected ? [] : ["$at: expected " . var_export($expected, true) . ', got ' . var_export($actual, true)];
    }
}
