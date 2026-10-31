<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Before;
use Windwalker\Queue\Driver\PdoQueueDriver;
use Windwalker\Queue\Queue;

/**
 * The QueueAdapterTest class.
 */
class QueuePdoTest extends QueueDatabaseTest
{
    #[Before]
    protected function createQueueInstance(): void
    {
        $conn = static::$db->getDriver()->getConnection();

        $this->instance = new Queue(new PdoQueueDriver($conn->get()));

        $conn->release();
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    protected static function setupDatabase(): void
    {
        self::createDatabase('pdo_mysql');
        self::importFromFile(__DIR__ . '/../resources/sql/queue_jobs.sql');
    }
}
