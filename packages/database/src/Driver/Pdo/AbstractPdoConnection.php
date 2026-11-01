<?php

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use PDO;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\DriverOptions;

/**
 * The PdoConnection class.
 */
abstract class AbstractPdoConnection extends AbstractConnection
{
    protected static string $name = 'pdo';

    /**
     * @var  string
     */
    protected static string $dbtype = '';

    protected static array $defaultAttributes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        if (!class_exists(PDO::class)) {
            return false;
        }

        return in_array(strtolower(static::$dbtype), PDO::getAvailableDrivers(), true);
    }

    /**
     * getDsn
     *
     * @param  array  $options
     *
     * @return  string
     */
    public static function getDsn(array $options): string
    {
        return DsnHelper::build($options, static::$dbtype);
    }

    /**
     * @return  array
     */
    public static function getDefaultAttributes(): array
    {
        return static::$defaultAttributes;
    }

    /**
     * @param  DriverOptions  $options
     *
     * @return  PDO
     */
    protected function doConnect(DriverOptions $options): PDO
    {
        $attrs = array_replace(
            static::getDefaultAttributes(),
            $options->driverOptions ?? []
        );

        return $this->pdoConnect(
            $options->dsn,
            $options->user ?? null,
            $options->password ?? null,
            $attrs
        );
    }

    /**
     * Instantiate the native PDO connection.
     *
     * Subclasses with a driver-specific PDO subclass (e.g. `Pdo\Sqlite`) should override this
     * and `new` that subclass directly instead of using `PDO::connect()` or a dynamic
     * class-string. This avoids a known PHP 8.4 regression in some builds where
     * `PDO::connect()` silently falls back to a plain `PDO` instance instead of the expected
     * driver-specific subclass.
     */
    protected function pdoConnect(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null): PDO
    {
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * @return  PDO|null
     */
    public function get(): ?PDO
    {
        return parent::get();
    }

    /**
     * @inheritDoc
     */
    public function disconnect(): mixed
    {
        $this->connection = null;

        return true;
    }

    /**
     * @return  string
     */
    public static function getDbType(): string
    {
        return static::$dbtype;
    }

    public function ping(): bool
    {
        try {
            $this->connect();

            $this->connection->query('SELECT 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
