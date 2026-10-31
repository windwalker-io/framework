<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Failer;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Queue\Failer\DatabaseQueueFailer;
use Windwalker\Queue\Failer\QueueFailerInterface;
use Windwalker\Test\Traits\DatabaseTestTrait;

/**
 * The DatabaseQueueFailerTest class.
 */
class DatabaseQueueFailerTest extends TestCase
{
    use DatabaseTestTrait;

    /**
     * @var QueueFailerInterface|null
     */
    protected ?QueueFailerInterface $instance = null;

    /**
     * @see  DatabaseQueueFailer::add
     */
    #[Test]
    public function addStoresFailedJobsAndReturnsLastId(): void
    {
        $this->instance->add(
            'test',
            'default',
            '{}',
            RuntimeException::class
        );
        $r = $this->instance->add(
            'hello',
            'world',
            '{}',
            RuntimeException::class
        );

        self::assertEquals(
            2,
            $r
        );

        $item = self::$db->select('*')
            ->from('queue_failed_jobs')
            ->where('id', 1)
            ->get();

        self::assertEquals(
            'test',
            $item->connection
        );
        self::assertEquals(
            '{}',
            $item->body
        );
        self::assertEquals(
            RuntimeException::class,
            $item->exception
        );
    }

    /**
     * @see  DatabaseQueueFailer::get
     */
    #[Test]
    public function getReturnsFailedJobById(): void
    {
        $item = $this->instance->get(2);

        self::assertEquals(
            2,
            $item['id']
        );

        self::assertEquals(
            'hello',
            $item['connection']
        );

        self::assertEquals(
            'world',
            $item['channel']
        );
    }

    /**
     * @see  DatabaseQueueFailer::all
     */
    #[Test]
    public function allIteratesFailedJobsInInsertOrder(): void
    {
        $items = $this->instance->all();

        self::assertEquals(
            ['test', 'hello'],
            array_column(iterator_to_array($items), 'connection')
        );
    }

    /**
     * @see  DatabaseQueueFailer::remove
     */
    #[Test]
    public function removeDeletesFailedJobById(): void
    {
        $this->instance->remove(1);

        self::assertNull(
            self::$db->select('*')
                ->from('queue_failed_jobs')
                ->where('id', 1)
                ->get()
        );
    }

    /**
     * @see  DatabaseQueueFailer::clear
     */
    #[Test]
    public function clearDeletesAllFailedJobs(): void
    {
        $this->instance->clear();

        self::assertEquals(
            [],
            self::$db->select('*')
                ->from('queue_failed_jobs')
                ->all()
                ->dump()
        );
    }

    /**
     * @see  DatabaseQueueFailer::isSupported
     */
    #[Test]
    public function isSupportedReturnsTrue(): void
    {
        self::assertTrue($this->instance->isSupported());
    }

    #[Before]
    protected function createFailerInstance(): void
    {
        $this->instance = new DatabaseQueueFailer(
            self::$db
        );
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    protected static function setupDatabase(): void
    {
        self::createDatabase('pdo_mysql');

        self::importFromFile(__DIR__ . '/../../resources/sql/queue_failed_jobs.sql');
    }
}
