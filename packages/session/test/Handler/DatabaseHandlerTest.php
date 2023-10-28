<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use Windwalker\Session\Handler\AbstractHandler;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\DatabaseHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Test\Traits\DatabaseTestTrait;

/**
 * The ArrayHandlerTest class.
 */
class DatabaseHandlerTest extends AbstractHandlerTest
{
    use DatabaseTestTrait;

    /**
     * @var DatabaseHandler
     */
    protected ?AbstractHandler $instance;

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    public function testUpdateTimestamp(): void
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

    protected function createInstance(): HandlerInterface
    {
        $this->instance = new DatabaseHandler(static::$db);

        self::$db->getTable('windwalker_sessions')->truncate();

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

        self::$db->getTable('windwalker_sessions')->drop();
        self::importFromFile(__DIR__ . '/../../resources/sql/mysql.sql');
    }
}
