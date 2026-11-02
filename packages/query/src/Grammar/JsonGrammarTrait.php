<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

/**
 * Trait JsonPathTrait
 */
trait JsonGrammarTrait
{
    public static function compileJsonPath(array $segments): string
    {
        $path = '$';

        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                $path .= "[$segment]";
            } else {
                $path .= '.' . $segment;
            }
        }

        return $path;
    }

    /**
     * Encode a value as the JSON candidate for containment checks.
     * Arrays are encoded as is (all elements must be contained),
     * any other value is treated as a single element.
     */
    public static function encodeJsonContainsValue(mixed $value): string
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return  list<scalar|null>
     */
    public static function normalizeJsonContainsElements(mixed $value): array
    {
        $elements = is_array($value) ? array_values($value) : [$value];

        if ($elements === []) {
            throw new \InvalidArgumentException('JSON contains value must not be an empty array.');
        }

        foreach ($elements as $element) {
            if (!is_scalar($element) && $element !== null) {
                throw new \InvalidArgumentException(
                    'This database only supports scalar elements in JSON contains.'
                );
            }
        }

        return $elements;
    }
}
