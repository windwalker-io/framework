<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Session\Handler\AbstractHandler;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\DatabaseHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Test\Traits\DatabaseTestTrait;

/**
 * The ArrayHandlerTest class.
 */
class DatabaseHandlerTest extends AbstractHandlerTestCase
{
    use DatabaseTestTrait;

    /**
     * @var  DatabaseHandler
     */
    protected ?AbstractHandler $instance;

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    #[Test]
    public function updateTimestampRefreshesStoredTime(): void
    {
        $session = $this->createSession();

        $lastTime = self::$db->select('time')
            ->from('windwalker_sessions')
            ->where('id', static::$sess1)
            ->result();

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();

        $newTime = self::$db->select('time')
            ->from('windwalker_sessions')
            ->where('id', static::$sess1)
            ->result();

        self::assertTrue($lastTime < $newTime);
    }

    #[Test]
    public function writeHandlerCustomizesColumns(): void
    {
        self::$db->execute('ALTER TABLE windwalker_sessions ADD COLUMN extra VARCHAR(50) NULL');

        try {
            $handler = new DatabaseHandler(
                self::$db,
                writeHandler: static fn(array $values, string $id, string $data) => $values + [
                    'extra' => 'v:' . strlen($data),
                ]
            );

            $handler->write('custom-id', 'abc');

            self::assertSame(
                'v:3',
                self::$db->select('extra')->from('windwalker_sessions')->where('id', 'custom-id')->result()
            );

            $handler->write('custom-id', 'abcdef');

            self::assertSame(
                'v:6',
                self::$db->select('extra')->from('windwalker_sessions')->where('id', 'custom-id')->result()
            );
        } finally {
            self::$db->execute('ALTER TABLE windwalker_sessions DROP COLUMN extra');
        }
    }

    protected function createInstance(): HandlerInterface
    {
        $this->prepareVfs();

        $this->instance = new DatabaseHandler(static::$db);

        self::$db->getTableManager('windwalker_sessions')->truncate();

        foreach ($this->prepareDefaultData() as $id => $item) {
            self::$db->getWriter()->insertOne(
                'windwalker_sessions',
                [
                    'id' => $id,
                    'data' => $item['data'],
                    'time' => $item['time'],
                ]
            );
        }

        return $this->instance;
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    protected static function setupDatabase(): void
    {
        self::createDatabase('pdo_mysql');

        self::$db->getTableManager('windwalker_sessions')->drop();
        self::importFromFile(__DIR__ . '/../../resources/sql/mysql.sql');
    }
}
