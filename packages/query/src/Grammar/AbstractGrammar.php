<?php

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use BadMethodCallException;
use InvalidArgumentException;
use Stringable;
use Windwalker\Query\Clause\AsClause;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ValueClause;
use Windwalker\Query\DefaultConnection;
use Windwalker\Query\Escaper;
use Windwalker\Query\Query;
use Windwalker\Utilities\TypeCast;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\value;

/**
 * The AbstractGrammar class.
 */
abstract class AbstractGrammar
{
    /**
     * @var  string
     */
    protected static string $name = '';

    /**
     * @var  array
     */
    public static array $nameQuote = ['"', '"'];

    /**
     * @var  string
     */
    public static string $nullDate = '0000-00-00 00:00:00';

    /**
     * @var  string
     */
    public static string $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var  Escaper
     */
    protected Escaper $escaper;

    /**
     * create
     *
     * @param  string|null  $name
     *
     * @return  static
     */
    public static function create(?string $name = null): BaseGrammar|static
    {
        if ($name === null) {
            if ($grammar = DefaultConnection::getGrammar()) {
                return $grammar;
            }
        }

        $class = sprintf('%s\%sGrammar', __NAMESPACE__, ucfirst((string) $name));

        if (!class_exists($class)) {
            $class = BaseGrammar::class;
        }

        return new $class();
    }

    /**
     * Method to get property Name
     *
     * @return  string
     */
    public static function getName(): string
    {
        return static::$name;
    }

    /**
     * Compile Query object to SQL string.
     *
     * @param  string  $type
     * @param  Query   $query
     *
     * @return  string
     */
    public function compile(string $type, Query $query): string
    {
        if ($type === '') {
            throw new InvalidArgumentException('Type shouldn\'t be empty string');
        }

        $method = 'compile' . ucfirst($type);

        if (!method_exists($this, $method)) {
            throw new BadMethodCallException(
                sprintf(
                    '%s dose not support "%s" compiled',
                    static::class,
                    $type
                )
            );
        }

        $sql = $this->$method($query);

        if ($with = $this->compileWith($query)) {
            $sql = $with . ' ' . $sql;
        }

        if ($prefix = $query->getPrefix()) {
            $sql = $prefix . ' ' . $sql;
        }

        if ($suffix = $query->getSuffix()) {
            $sql .= ' ' . $suffix;
        }

        return $sql;
    }

    public function compileWith(Query $query): string
    {
        $with = $query->getWith();

        if (!$with || !count($with->getElements())) {
            return '';
        }

        $keyword = $query->getRecursive() && $this->supportsRecursiveKeyword() ? 'WITH RECURSIVE' : 'WITH';

        return $with->setName($keyword)->render();
    }

    public function compileMaterializedHint(?bool $materialized): string
    {
        return '';
    }

    public function supportsTableAliasAs(): bool
    {
        return true;
    }

    protected function supportsRecursiveKeyword(): bool
    {
        return true;
    }

    public function compileSelect(Query $query): string
    {
        $sql['select'] = (string) $query->getSelect();

        if ($union = $query->getUnion()) {
            // Is full union
            if (!$query->getSelect()) {
                $union->setName('()');

                unset($sql['group']);
            }

            $sql['union'] = (string) $union;
        } else {
            $sql['select'] = $sql['select'] ?: 'SELECT *';
        }

        if ($form = $query->getFrom()) {
            $sql['from'] = $form;
        }

        if ($join = $query->getJoin()) {
            $sql['join'] = $join;
        }

        if ($where = $query->getWhere()) {
            $sql['where'] = $where;
        }

        if ($group = $query->getGroup()) {
            $sql['group'] = $group;
        }

        if ($having = $query->getHaving()) {
            $sql['having'] = $having;
        }

        // Only order and limit can after union
        if ($order = $query->getOrder()) {
            $sql['order'] = $order;
        }

        $sql = $this->compileLimit($query, $sql);

        return implode(' ', $sql);
    }

    public function compileInsert(Query $query): string
    {
        $sql['insert'] = $query->getInsert();

        if ($set = $query->getSet()) {
            $sql['set'] = $set;
        } else {
            if ($columns = $query->getColumns()) {
                $sql['columns'] = $columns;
            }

            if ($values = $query->getValues()) {
                $sql['values'] = $values;
            }
        }

        return trim(implode(' ', $sql));
    }

    public function compileUpdate(Query $query): string
    {
        $sql['update'] = $query->getUpdate();

        if ($join = $query->getJoin()) {
            $sql['join'] = $join;
        }

        if ($set = $query->getSet()) {
            $sql['set'] = $set;
        }

        if ($where = $query->getWhere()) {
            $sql['where'] = $where;
        }

        return trim(implode(' ', $sql));
    }

    public function compileDelete(Query $query): string
    {
        $sql['delete'] = $query->getDelete();

        if ($from = $query->getFrom()) {
            if (!$query->getJoin()) {
                foreach ($from->getElements() as $element) {
                    if ($element instanceof AsClause) {
                        $element->alias(false);
                    }
                }
            }

            $sql['from'] = $from;
        }

        if ($join = $query->getJoin()) {
            $sql['join'] = $join;
        }

        if ($where = $query->getWhere()) {
            $sql['where'] = $where;
        }

        return trim(implode(' ', $sql));
    }

    public function compileCustom(Query $query): string
    {
        return (string) $query->getSql();
    }

    public static function quoteNameMultiple(mixed $name, bool $ignoreDot = false): mixed
    {
        if ($name instanceof RawWrapper) {
            return value($name);
        }

        if ($name instanceof Clause) {
            return $name->setElements(static::quoteNameMultiple($name->elements, $ignoreDot));
        }

        if (is_iterable($name)) {
            foreach ($name as &$n) {
                $n = static::quoteNameMultiple($n, $ignoreDot);
            }

            return $name;
        }

        return static::quoteName((string) $name, $ignoreDot);
    }

    public static function quoteName(string|Stringable $name, bool $ignoreDot = false): string
    {
        if ($name instanceof RawWrapper) {
            return value($name);
        }

        $name = (string) $name;

        if ($name === '*') {
            return $name;
        }

        if (stripos($name, ' as ') !== false) {
            [$name, $alias] = (array) preg_split('/ as /i', $name);

            return static::quoteName($name) . ' AS ' . static::quoteName($alias);
        }

        if (!$ignoreDot && str_contains($name, '.')) {
            $name = trim($name, '.');

            return implode(
                '.',
                array_map(
                    static fn($v) => static::quoteName($v),
                    explode('.', $name)
                )
            );
        }

        $closingQuote = static::$nameQuote[1];
        $name = str_replace($closingQuote, $closingQuote . $closingQuote, $name);

        return static::$nameQuote[0] . $name . $closingQuote;
    }

    /**
     * compileLimit
     *
     * @param  Query  $query
     * @param  array  $sql
     *
     * @return  array
     */
    public function compileRandomOrder(): string
    {
        return 'RAND()';
    }

    /**
     * Compile an expression that maps a column to its position in the given values.
     * Unlisted values get a position after the last listed one.
     *
     * @param  Query   $query
     * @param  string  $column  Already quoted column.
     * @param  array   $values  Non-empty list.
     *
     * @return  Clause
     */
    public function compileOrderByField(Query $query, string $column, array $values): Clause
    {
        $cases = [];

        foreach ($values as $i => $value) {
            $cases[] = $value === null
                ? static::clause('', ['WHEN', $column, 'IS NULL', 'THEN', $i + 1])
                : static::clause('', ['WHEN', $column, '=', $query->valueize($value, false), 'THEN', $i + 1]);
        }

        return static::clause('CASE', [...$cases, 'ELSE', count($values) + 1, 'END']);
    }

    public function compileLimit(Query $query, array $sql): array
    {
        $limit = $query->getLimit();
        $offset = $query->getOffset();

        if ($limit !== null) {
            $limitOffset = new Clause('LIMIT', (int) $limit, ', ');

            if ($offset !== null) {
                $limitOffset->prepend($offset);
            }

            $sql['limit'] = $limitOffset;
        }

        return $sql;
    }

    /**
     * If no connection set, we escape it with default function.
     *
     * @param  string  $text
     *
     * @return  string
     */
    public static function localEscape(string $text): string
    {
        $text = str_replace("'", "''", $text);

        return addcslashes($text, "\000\n\r\\\032");
    }

    public static function nullDate(): string
    {
        return static::$nullDate;
    }

    public static function dateFormat(): string
    {
        return static::$dateFormat;
    }

    /**
     * clause
     *
     * @param  string  $name
     * @param  mixed   $elements
     * @param  string  $glue
     *
     * @return  Clause
     */
    public static function clause(string $name = '', $elements = [], string $glue = ' '): Clause
    {
        return new Clause($name, $elements, $glue);
    }

    /**
     * build
     *
     * @param  string|null  ...$args
     *
     * @return  Clause
     */
    public static function build(?string ...$args): Clause
    {
        $clause = static::clause('', [], ' ');

        foreach ($args as $arg) {
            $clause->append($arg);
        }

        return $clause;
    }

    public static function buildConfig(array $elements, string $separator = '='): string
    {
        $elements = array_filter(TypeCast::mapAs($elements, 'string'), 'strlen');
        $items = [];

        foreach ($elements as $key => $element) {
            $items[] = $key . $separator . $element;
        }

        return implode(' ', $items);
    }

    /**
     * Compile an upsert (insert or update on conflict) SQL string from the query state.
     *
     * @param  Query  $query
     *
     * @return  string
     */
    public function compileUpsert(Query $query): string
    {
        throw new BadMethodCallException(
            sprintf('%s dose not support "upsert" compiled', static::class)
        );
    }

    /**
     * Resolve the upsert state of a query. All names are quoted.
     *
     * - `data`: quoted column => value expression.
     * - `keys`: quoted conflict keys.
     * - `update`: quoted column => custom value expression, `null` means use the inserted value.
     *   Fallback to all non-key columns, and then to the keys if there is nothing to update.
     *
     * @param  Query  $query
     *
     * @return    array{
     *           table: string,
     *           data: array<string, mixed>,
     *           keys: string[],
     *           update: array<string, mixed>,
     *           returning: string|null
     *           }
     */
    protected function resolveUpsert(Query $query): array
    {
        $upsert = $query->getUpsert() ?? ['keys' => [], 'update' => [], 'returning' => null];
        $data = [];

        if ($set = $query->getSet()) {
            foreach ($set->getElements() as $item) {
                [$column, , $value] = $item->getElements();

                $data[(string) $column] = $value;
            }
        } else {
            $columns = $query->getColumns();
            $values = $query->getValues();

            if ($values !== null && !$values instanceof Clause) {
                throw new InvalidArgumentException('Upsert does not support INSERT ... SELECT.');
            }

            $row = $values?->getElements()[0] ?? null;

            if ($columns && $row) {
                foreach (array_values($columns->getElements()) as $i => $column) {
                    $data[(string) $column] = $row->getElements()[$i]
                        ?? throw new InvalidArgumentException('Upsert columns and values count not match.');
                }
            }
        }

        if ($data === []) {
            throw new InvalidArgumentException('Upsert requires data, please use set() or columns() and values().');
        }

        $keys = array_map(fn($key) => (string) $query->quoteName($key), $upsert['keys']);
        $update = [];

        foreach ($upsert['update'] as $column => $value) {
            $update[(string) $query->quoteName($column)] = $value;
        }

        if ($update === []) {
            foreach (array_keys($data) as $column) {
                if (!in_array($column, $keys, true)) {
                    $update[$column] = null;
                }
            }
        }

        if ($update === []) {
            foreach ($keys as $key) {
                $update[$key] = null;
            }
        }

        return [
            'table' => (string) $query->getInsert()->getElements()[0],
            'data' => $data,
            'keys' => $keys,
            'update' => $update,
            'returning' => $upsert['returning'] !== null ? (string) $query->quoteName($upsert['returning']) : null,
        ];
    }

    /**
     * Build the `(columns)` and `(values)` clauses of an upsert.
     *
     * @param  Query  $query
     * @param  array  $data
     *
     * @return  array{0: Clause, 1: Clause}
     */
    protected function compileUpsertColumnsAndValues(Query $query, array $data): array
    {
        return [
            $query->clause('()', array_keys($data), ','),
            $query->clause('()', array_values($data), ','),
        ];
    }

    /**
     * Get a value that is already used in the VALUES clause for another place of the same statement.
     *
     * Named placeholders can not be repeated when emulate prepares is disabled,
     * so bound values will be bound again with a new placeholder.
     *
     * @param  Query  $query
     * @param  mixed  $value
     *
     * @return  mixed
     */
    protected function reuseUpsertValue(Query $query, mixed $value): mixed
    {
        if (!$value instanceof ValueClause) {
            return $value;
        }

        $inner = $value->getValue();

        if ($inner instanceof RawWrapper || $inner instanceof Query) {
            return $value;
        }

        return $query->valueize($inner, false);
    }

    /**
     * Build the MERGE `ON` condition from conflict keys.
     *
     * @param  Query  $query
     * @param  array  $keys
     * @param  array  $data
     *
     * @return  Clause
     */
    protected function compileUpsertMergeOn(Query $query, array $keys, array $data): Clause
    {
        if ($keys === []) {
            throw new InvalidArgumentException('Upsert requires conflict keys, please use onConflict().');
        }

        $on = $query->clause('()', [], ' AND ');

        foreach ($keys as $key) {
            $on->append(
                $key . ' = ' . $this->reuseUpsertValue(
                    $query,
                    $data[$key] ?? throw new InvalidArgumentException("Key field: $key not exists in data.")
                )
            );
        }

        return $on;
    }

    /**
     * @param  Escaper  $escaper
     *
     * @return  static  Return self to support chaining.
     */
    public function setEscaper(Escaper $escaper): static
    {
        $this->escaper = $escaper;

        return $this;
    }

    public function createQuery(): Query
    {
        return new Query($this->escaper, $this);
    }
}
