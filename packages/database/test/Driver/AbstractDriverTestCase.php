<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Event\QueryEndEvent;
use Windwalker\Database\Exception\DatabaseQueryException;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\AbstractDatabaseDriverTestCase;
use Windwalker\Utilities\TypeCast;

/**
 * The AbstractDriverTest class.
 */
abstract class AbstractDriverTestCase extends AbstractDatabaseDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static string $driverName = '';

    protected static ?AbstractDriver $driver;

    /**
     * @see  AbstractDriver::prepare
     */
    #[Test]
    public function prepare(): void
    {
        $st = static::$driver->prepare('SELECT * FROM ww_flower WHERE id <= :id')
            ->bind('id', 2);

        self::assertEquals(
            [
                [
                    'id' => '1',
                    'title' => 'Alstroemeria',
                ],
                [
                    'id' => '2',
                    'title' => 'Amaryllis',
                ],
            ],
            $st->all()
                ->mapProxy()
                ->only(['id', 'title'])
                ->dump(true)
        );
    }

    #[Test]
    public function prepareBounded(): void
    {
        // Bind param
        $id = 1;
        $st = static::$driver->prepare('SELECT * FROM ww_flower WHERE id = :id')
            ->bindParam(':id', $id);

        self::assertEquals(
            'Alstroemeria',
            $st->get()->title
        );
        $id++;
        self::assertEquals(
            'Amaryllis',
            $st->get()->title
        );
        $id++;
        self::assertEquals(
            'Anemone',
            $st->get()->title
        );
    }

    #[Test]
    public function prepareWithQuery(): void
    {
        $id = 1;

        $query = static::createQuery()
            ->select('*')
            ->from('ww_flower')
            ->whereRaw('id = :id')
            ->bindParam(':id', $id);

        // Bind param
        $st = static::$driver->prepare($query);

        self::assertEquals(
            'Alstroemeria',
            $st->get()->title
        );
        $id++;
        self::assertEquals(
            'Amaryllis',
            $st->get()->title
        );
        $id++;
        self::assertEquals(
            'Anemone',
            $st->get()->title
        );
    }

    /**
     * @see  AbstractDriver::execute
     */
    #[Test]
    public function prepareAndExecute(): void
    {
        $st = static::$driver->prepare('SELECT * FROM ww_flower WHERE id <= ?')
            ->execute([2]);

        self::assertEquals(
            [
                [
                    'id' => '1',
                    'title' => 'Alstroemeria',
                ],
                [
                    'id' => '2',
                    'title' => 'Amaryllis',
                ],
            ],
            $st->all()
                ->mapProxy()
                ->only(['id', 'title'])
                ->dump(true)
        );

        $st = static::$driver->prepare('SELECT * FROM ww_flower WHERE id IN(?, ?, ?)')
            ->execute([1, 2, 3]);

        self::assertEquals(
            [
                [
                    'id' => '1',
                    'title' => 'Alstroemeria',
                ],
                [
                    'id' => '2',
                    'title' => 'Amaryllis',
                ],
                [
                    'id' => '3',
                    'title' => 'Anemone',
                ],
            ],
            $st->all()
                ->mapProxy()
                ->only(['id', 'title'])
                ->dump(true)
        );
    }

    /**
     * @see  AbstractDriver::execute
     */
    #[Test]
    public function execute(): void
    {
        $st = static::$driver->execute(
            'UPDATE ww_flower SET params = ? WHERE id <= ?',
            [
                'hello',
                3,
            ]
        );

        self::assertEquals(
            'hello',
            static::$driver->prepare(
                'SELECT params FROM ww_flower WHERE id = 1'
            )
                ->result()
        );

        self::assertEquals(
            3,
            $st->countAffected()
        );
    }

    #[Test]
    public function executeInsert(): void
    {
        $st = static::$driver->execute(
            'INSERT INTO ww_flower (title, meaning, params) VALUES (?, ?, ?)',
            [
                'Test',
                'YO',
                '{}',
            ]
        );

        self::assertEquals(
            86,
            $st->lastInsertId()
        );

        self::assertEquals(
            1,
            $st->countAffected()
        );
    }

    #[Test]
    public function includesSqlInExceptionWhenQueryFails(): void
    {
        $sql = 'SELECT * FROM notexists WHERE foo = 123';

        $this->expectException(DatabaseQueryException::class);
        $this->expectExceptionMessageMatches(sprintf('/(%s)/', preg_quote($sql, '/')));

        static::$driver->getOptions()->debug = true;

        static::$driver->prepare($sql)->get();
    }

    #[Test]
    public function emitsQueryEndEvent(): void
    {
        $stmt = static::$driver->prepare(
            $q = static::createQuery()
                ->select('*')
                ->from('ww_flower')
                ->where('id', 'in', [1, 2, 3])
                ->where('title', '!=', 'Hello')
        );

        $data = [];

        $stmt->on(
            QueryEndEvent::class,
            static function (QueryEndEvent $event) use (&$data) {
                $data['end'] = $event->query->render(true);
            }
        );

        $stmt->execute()->close();

        self::assertSqlEquals(
            $q->render(true),
            $data['end']
        );
    }

    #[Test]
    public function iterator(): void
    {
        $st = static::$driver->prepare('SELECT id, title FROM ww_flower WHERE id <= ?')
            ->execute([2]);

        $it = $st->getIterator();

        self::assertEquals(
            [
                [
                    'id' => '1',
                    'title' => 'Alstroemeria',
                ],
                [
                    'id' => '2',
                    'title' => 'Amaryllis',
                ],
            ],
            TypeCast::toArray($it, true)
        );
    }

    /**
     * @see  AbstractDriver::getPlatformName
     */
    #[Test]
    public function getPlatformName(): void
    {
        self::assertEquals(
            static::platformName(),
            static::$driver->getPlatformName()
        );
    }

    /**
     * @see  AbstractDriver::disconnectAll
     */
    #[Test]
    public function disconnect(): void
    {
        static::$driver->disconnectAll();

        self::assertCount(0, static::$driver->getPool());
    }

    /**
     * @see  AbstractDriver::quote
     */
    #[Test]
    public function quote(): void
    {
        self::assertEquals(
            "'foo''s #hello --options'",
            static::$driver->quote("foo's #hello --options")
        );
    }

    /**
     * @see  AbstractDriver::escape
     */
    #[Test]
    public function escape(): void
    {
        self::assertEquals(
            "foo''s #hello --options",
            static::$driver->escape("foo's #hello --options")
        );
    }

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$driver = self::createDriver();

        if (!static::$driver->isSupported()) {
            self::markTestSkipped('Driver: ' . static::$driverName . ' not available.');
        }

        static::$driver->setPlatformName(static::platformName());
    }

    protected static function createDriver(?array $params = null): AbstractDriver
    {
        $params = $params ?? self::getTestParams();
        $params['driver'] = static::$driverName;

        $options = static::prepareTestOptions($params);

        return new DatabaseFactory()->createDriver(static::$driverName, $options);
    }

    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../stub/' . static::platformName() . '.sql');
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        static::$driver->disconnectAll();
        static::$driver = null;
    }
}
