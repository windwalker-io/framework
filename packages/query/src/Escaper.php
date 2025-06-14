<?php

declare(strict_types=1);

namespace Windwalker\Query;

use PDO;
use UnexpectedValueException;
use WeakReference;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Platform\MySQLPlatform;
use Windwalker\ORM\ORM;
use Windwalker\Utilities\Str;

/**
 * The Escaper class.
 */
class Escaper
{
    /**
     * @var WeakReference<PDO|callable|mixed>|null
     */
    protected ?WeakReference $connection = null;

    /**
     * @var Query|null
     */
    protected ?Query $query = null;

    /**
     * Escaper constructor.
     *
     * @param  PDO|callable|object  $connection
     * @param  Query|null            $query
     */
    public function __construct(mixed $connection, ?Query $query = null)
    {
        $this->connection = $connection ? WeakReference::create($connection) : null;
        $this->query = $query;
    }

    public function escape(string $value): string
    {
        return static::tryEscape($this->getConnection(), $value);
    }

    public function quote(string $value): string
    {
        return static::tryQuote($this->getConnection(), $value);
    }

    /**
     * escape
     *
     * @param  PDO|callable|mixed  $escaper
     * @param  int|string           $value
     *
     * @return  string
     */
    public static function tryEscape(mixed $escaper, string|int $value): string
    {
        if ($escaper instanceof PDO) {
            return static::stripQuote((string) $escaper->quote((string) $value));
        }

        if ($escaper instanceof AbstractDriver) {
            return $escaper->escape((string) $value);
        }

        if ($escaper instanceof DatabaseAdapter) {
            return $escaper->escape((string) $value);
        }

        if ($escaper instanceof ORM) {
            return $escaper->getDb()->escape((string) $value);
        }

        if (is_callable($escaper)) {
            return $escaper($value, [static::class, 'stripQuote']);
        }

        return $escaper->escape($value);
    }

    /**
     * quote
     *
     * @param  PDO|callable|mixed  $escaper
     * @param  int|string           $value
     *
     * @return  string
     */
    public static function tryQuote(mixed $escaper, string|int $value): string
    {
        // PDO has quote method, directly use it.
        if ($escaper instanceof PDO) {
            return (string) $escaper->quote((string) $value);
        }

        if ($escaper instanceof DatabaseAdapter) {
            return $escaper->getDriver()->quote((string) $value);
        }

        if ($escaper instanceof ORM) {
            return $escaper->getDb()->quote((string) $value);
        }

        return "'" . static::tryEscape($escaper, $value) . "'";
    }

    public static function isMySQL(mixed $escaper): bool
    {
        if ($escaper instanceof self) {
            $escaper = $escaper->getConnection();
        }

        // PDO has quote method, directly use it.
        if ($escaper instanceof PDO) {
            return str_contains($escaper->getAttribute(PDO::ATTR_SERVER_VERSION), 'mysql');
        }

        if ($escaper instanceof ORM) {
            $escaper = $escaper->getDb();
        }

        if ($escaper instanceof DatabaseAdapter) {
            $platform = $escaper->getPlatform();

            if ($platform instanceof MySQLPlatform) {
                return !$platform->isMariaDB();
            }

            return false;
        }

        return false;
    }

    /**
     * stripQuote
     *
     * @param  string  $value
     *
     * @return  string
     */
    public static function stripQuote(string $value): string
    {
        return substr(
            substr(
                (string) $value,
                0,
                -1
            ),
            1
        );
    }

    /**
     * stripQuoteIfExists
     *
     * @param  string|null  $value
     * @param  string       $sign
     * @param  string|null  $sign2
     *
     * @return string|null
     */
    public static function stripQuoteIfExists(?string $value, string $sign = "'", ?string $sign2 = null): ?string
    {
        if ($value === null) {
            return $value;
        }

        $sign2 ??= $sign;

        if (Str::startsWith($value, $sign) && Str::endsWith($value, $sign2)) {
            return static::stripQuote($value);
        }

        return $value;
    }

    /**
     * Method to get property Connection
     *
     * @return  mixed
     */
    public function getConnection(): mixed
    {
        if ($this->connection instanceof WeakReference) {
            $conn = $this->connection->get();

            if ($conn === null) {
                throw new UnexpectedValueException(
                    'Escaper connection is NULL, the reference object has been destroyed.'
                );
            }
        }

        return $conn ?? [$this->query->getGrammar(), 'localEscape'];
    }

    /**
     * Method to set property connection
     *
     * @param  mixed  $connection
     *
     * @return  static  Return self to support chaining.
     */
    public function setConnection(mixed $connection): static
    {
        if ($connection instanceof DatabaseAdapter) {
            $connection = $connection->getDriver();
        }

        if (!$connection instanceof WeakReference) {
            $connection = WeakReference::create($connection);
        }

        $this->connection = $connection;

        return $this;
    }
}
