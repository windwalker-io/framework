<?php

declare(strict_types=1);

namespace Windwalker\Database\Test;

use Asika\SqlSplitter\SqlSplitter;
use LogicException;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\Database\Driver\Pdo\AbstractPdoConnection;
use Windwalker\Database\Driver\Pdo\DsnHelper;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Reseter\AbstractReseter;
use Windwalker\Query\Escaper;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Query;
use Windwalker\Test\Traits\QueryTestTrait;

/**
 * The AbstractDatabaseTestCase class.
 *
 * @codeCoverageIgnore
 */
abstract class AbstractDatabaseDriverTestCase extends TestCase
{
    use QueryTestTrait;

    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static ?string $dbname = '';

    /**
     * @var  PDO
     */
    protected static ?PDO $baseConn;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $params = static::getTestParams();

        if (!$params) {
            self::markTestSkipped('DSN of ' . static::platformName() . ' not available for test case: ' . static::class);
        }

        $options = DriverOptions::wrap($params);

        if (static::$platform === DbPlatform::SQLServer) {
            $options->tls = false;
        }

        $platform = static::platformName();

        $platform = DatabaseFactory::getDriverShortName($platform);

        /** @var AbstractPdoConnection|string $connClass */
        $connClass = 'Windwalker\Database\Driver\Pdo\Pdo' . ucfirst($platform) . 'Connection';

        if (!class_exists($connClass) || !is_subclass_of($connClass, AbstractPdoConnection::class)) {
            throw new LogicException(
                sprintf(
                    '%s should exists and extends %s',
                    $connClass,
                    AbstractPdoConnection::class
                )
            );
        }

        $reseter = AbstractReseter::create(static::$platform);

        static::$dbname = $options->dbname;

        // Oracle dbname is the service name, it is required to connect.
        if (static::$platform !== DbPlatform::Oracle) {
            $options->dbname = null;
        }

        $pdo = static::createBaseConnect($options, $connClass);

        if (static::getDbSuffix() !== '') {
            static::registerDropDatabase(clone $options, $connClass, static::$dbname, static::$platform);
        }

        $reseter->createDatabase($pdo, static::$dbname);

        // Disconnect.
        $pdo = null;

        $options->dbname = static::$dbname;

        static::$baseConn = static::createBaseConnect($options, $connClass);

        $reseter->clearAllTables(static::$baseConn, static::$dbname);

        static::setupDatabase();
    }

    /**
     * Drop the suffixed database when the process ends, even if tests error out or die.
     */
    protected static function registerDropDatabase(
        DriverOptions $options,
        string $connClass,
        string $dbname,
        DbPlatform $platform
    ): void {
        static $registered = [];

        $key = $platform->name . ':' . $dbname;

        if (isset($registered[$key])) {
            return;
        }

        $registered[$key] = true;

        register_shutdown_function(static function () use ($options, $connClass, $dbname, $platform) {
            try {
                $options->dbname = null;
                $pdo = static::createBaseConnect($options, $connClass);

                AbstractReseter::create($platform)->dropDatabase($pdo, $dbname);
            } catch (\Throwable) {
                // Ignore cleanup errors.
            }
        });
    }

    protected static function dropSuffixedDatabase(): void
    {
        if (static::getDbSuffix() === '' || !static::$dbname) {
            return;
        }

        $options = DriverOptions::wrap(static::getTestParams());
        $options->dbname = null;
        $platform = DatabaseFactory::getDriverShortName(static::platformName());
        $connClass = 'Windwalker\\Database\\Driver\\Pdo\\Pdo' . ucfirst($platform) . 'Connection';

        try {
            AbstractReseter::create(static::$platform)
                ->dropDatabase(static::createBaseConnect($options, $connClass), static::$dbname);
        } catch (\Throwable) {
            // Shutdown handler will retry.
        }
    }

    protected static function createBaseConnect(DriverOptions $options, string $connClass): PDO
    {
        /** @var class-string<AbstractConnection> $connClass */
        $options = $connClass::prepareDbOptions(clone $options);

        $pdo = new PDO(
            $options->dsn,
            $options->user ?? null,
            $options->password ?? null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );

        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'oci') {
            // Oracle returns upper-case column names, align with PdoOciConnection.
            $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        }

        return $pdo;
    }

    protected static function prepareTestOptions(DriverOptions|array $options): DriverOptions
    {
        $options = DriverOptions::wrap($options);

        // Sometimes if SQLServ DB running in container, will use self-signed cert.
        // We must force trust it, otherwise will get error message.
        if (static::$platform === DbPlatform::SQLServer) {
            $options->tls['verify'] = false;
        }

        return $options;
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    abstract protected static function setupDatabase(): void;

    /**
     * importFromFile
     *
     * @param  string  $file
     *
     * @return  void
     */
    protected static function importFromFile(string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException('File not found: ' . $file);
        }

        self::importIterator(
            SqlSplitter::splitSqlString(
                file_get_contents($file)
            )
        );
    }

    /**
     * importIterator
     *
     * @param  iterable  $queries
     *
     * @return  void
     */
    protected static function importIterator(iterable $queries): void
    {
        foreach ($queries as $query) {
            if (trim($query) === '') {
                continue;
            }

            try {
                static::$baseConn->exec($query);
            } catch (PDOException $e) {
                throw new PDOException(
                    $e->getMessage() . ' - SQ: ' . $query,
                    (int) $e->getCode(),
                    $e
                );
            }
        }
    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        // static::$baseConn->exec('DROP DATABASE ' . static::qn(static::$dbname));

        //        static::$baseConn = null;
    }

    /**
     * getTestParams
     *
     * @return  array
     */
    protected static function getTestParams(): array
    {
        $const = 'WINDWALKER_TEST_DB_DSN_' . strtoupper(static::platformName());

        // First let's look to see if we have a DSN defined or in the environment variables.
        if (defined($const) || getenv($const)) {
            $dsn = (defined($const) ? constant($const) : getenv($const));

            $params = DsnHelper::extract($dsn);

            return static::applyDbSuffix($params);
        }

        return [];
    }

    /**
     * Append `WINDWALKER_TEST_DB_SUFFIX` env to the database name, so concurrent
     * phpunit processes can use isolated databases.
     */
    public static function getDbSuffix(): string
    {
        if (static::$platform === DbPlatform::Oracle) {
            return '';
        }

        $suffix = (string) (getenv('WINDWALKER_TEST_DB_SUFFIX') ?: '');

        return preg_replace('/[^A-Za-z0-9_]/', '_', $suffix);
    }

    protected static function applyDbSuffix(array $params): array
    {
        $suffix = static::getDbSuffix();

        if ($suffix === '' || empty($params['dbname'])) {
            return $params;
        }

        if (static::$platform === DbPlatform::SQLite) {
            $info = pathinfo($params['dbname']);
            $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
            $dir = $info['dirname'] !== '.' ? $info['dirname'] . '/' : '';
            $params['dbname'] = $dir . $info['filename'] . '_' . $suffix . $ext;
        } else {
            $params['dbname'] .= '_' . $suffix;
        }

        return $params;
    }

    /**
     * getGrammar
     *
     * @param  mixed  $escaper
     *
     * @return  AbstractGrammar
     */
    public static function getGrammar(mixed $escaper = null): AbstractGrammar
    {
        $grammar = AbstractGrammar::create(static::platformName());

        if ($escaper) {
            $grammar->setEscaper(new Escaper($escaper));
        }

        return $grammar;
    }

    protected static function platformName(): string
    {
        return static::$platform->name;
    }

    public static function createQuery($escaper = null): Query
    {
        return new Query($escaper ?: static::$baseConn, static::getGrammar());
    }

    /**
     * quote
     *
     * @param  string  $text
     *
     * @return  string
     */
    protected static function qn(string $text): string
    {
        return static::getGrammar()::quoteName($text);
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$baseConn = null;

        static::dropSuffixedDatabase();
    }
}
