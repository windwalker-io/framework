<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

use function Windwalker\Query\qn;

/**
 * Trait JsonPathTrait
 */
trait JsonGrammarTrait
{
    /**
     * Normalize cast type of JSON selector (`foo->bar::int`) to one of
     * int, float, decimal, string, datetime, date.
     */
    public static function normalizeJsonCast(string $cast): string
    {
        $normalized = match (strtolower($cast)) {
            'int', 'integer' => 'int',
            'float', 'double' => 'float',
            'decimal', 'numeric' => 'decimal',
            'string', 'text' => 'string',
            'datetime' => 'datetime',
            'date' => 'date',
            default => throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported JSON cast type "%s", supported: int, float, decimal, string, datetime, date.',
                    $cast
                )
            ),
        };

        return $normalized;
    }

    public static function encodeJsonValue(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Compile segments to a PostgreSQL text array literal, e.g. `{foo,bar,1}`.
     */
    public static function compileJsonPathArray(array $segments): string
    {
        $elements = [];

        foreach ($segments as $segment) {
            $segment = (string) $segment;

            if (preg_match('/^([A-Za-z_$][\w$]*)((?:\[\d+\])+)$/', $segment, $matches)) {
                $elements[] = $matches[1];

                preg_match_all('/\d+/', $matches[2], $indexes);

                array_push($elements, ...$indexes[0]);

                continue;
            }

            if (preg_match('/^"(.*)"$/s', $segment, $matches)) {
                $segment = stripcslashes($matches[1]);
            }

            $elements[] = $segment;
        }

        return '{' . implode(',', array_map(
            static fn(string $element) => '"' . addcslashes($element, '"\\') . '"',
            $elements
        )) . '}';
    }

    public function supportsJsonSelectLength(): bool
    {
        return true;
    }

    public static function compileJsonPath(array $segments): string
    {
        $path = '$';

        foreach ($segments as $segment) {
            $segment = (string) $segment;

            if (ctype_digit($segment)) {
                $path .= "[$segment]";
            } elseif (
                preg_match('/^[A-Za-z_$][\w$]*(\[(\d+|\*)\])*$/', $segment)
                || preg_match('/^"(?:[^"\\\\]|\\\\.)*"$/', $segment)
            ) {
                // Plain identifier (optionally with array index) or already double-quoted key
                $path .= '.' . $segment;
            } else {
                $path .= '."' . addcslashes($segment, '"\\') . '"';
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

    /**
     * Compile `FUNC(column, path, ...$extra)` with the JSON path as a bound value.
     */
    protected function compileJsonPathFunction(
        Query $query,
        string $function,
        string $column,
        array $paths,
        mixed ...$extra
    ): Clause {
        return $query->expr(
            $function . '()',
            qn($column, $query),
            $query->valueize(static::compileJsonPath($paths), false),
            ...$extra
        );
    }

    /**
     * Normalize the string value of the deprecated compileJsonContains().
     */
    protected static function normalizeLegacyJsonContainsValue(string $value): string
    {
        return is_json($value) ? $value : json_encode((array) $value, JSON_THROW_ON_ERROR);
    }
}
