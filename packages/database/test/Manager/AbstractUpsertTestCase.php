<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\WriterManager;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * Platform-agnostic upsert() tests. Subclasses choose the platform/driver and provide the DDL.
 */
abstract class AbstractUpsertTestCase extends AbstractDatabaseTestCase
{
    protected WriterManager $writer;

    /**
     * Should create `ww_upsert_items(id auto increment PK, title unique NOT NULL, state int NULL)`.
     */
    abstract protected static function createUpsertTable(): void;

    protected static function setupDatabase(): void
    {
        static::createUpsertTable();
    }

    #[Before]
    protected function prepareWriter(): void
    {
        self::$baseConn->exec('DELETE FROM ww_upsert_items');

        $this->writer = self::$db->getWriter();
    }

    protected function fetchRows(): array
    {
        return self::$baseConn->query('SELECT title, state FROM ww_upsert_items ORDER BY title')
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    #[Test]
    public function upsertInsertsThenUpdatesOnConflict(): void
    {
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 1], 'title');
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 2], 'title');

        $rows = $this->fetchRows();

        self::assertCount(1, $rows);
        self::assertEquals(2, $rows[0]['state']);
    }

    #[Test]
    public function upsertWithLimitedUpdateFields(): void
    {
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 1], 'title');
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 5], 'title', []);

        self::assertEquals(1, $this->fetchRows()[0]['state']);
    }

    #[Test]
    public function upsertWithAssocUpdateFieldsUsesCustomValues(): void
    {
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 1], 'title');
        $this->writer->upsert(
            'ww_upsert_items',
            ['title' => 'A', 'state' => 5],
            'title',
            ['state' => 99]
        );

        self::assertEquals(99, $this->fetchRows()[0]['state']);
    }

    #[Test]
    public function upsertWithMixedUpdateFields(): void
    {
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 1], 'title');
        $this->writer->upsert(
            'ww_upsert_items',
            ['title' => 'A', 'state' => 5],
            'title',
            ['title', 'state' => 7]
        );

        self::assertEquals(7, $this->fetchRows()[0]['state']);
    }

    #[Test]
    public function upsertWithCallbackUpdateFields(): void
    {
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 1], 'title');

        $received = null;

        $this->writer->upsert(
            'ww_upsert_items',
            ['title' => 'A', 'state' => 5],
            'title',
            function (array $data, array $keys) use (&$received) {
                $received = [$data, $keys];

                return ['state' => $data['state'] + 100];
            }
        );

        self::assertSame([['title' => 'A', 'state' => 5], ['title']], $received);
        self::assertEquals(105, $this->fetchRows()[0]['state']);
    }

    #[Test]
    public function upsertSkipsNullsWhenUpdateNullsIsFalse(): void
    {
        $this->writer->upsert('ww_upsert_items', ['title' => 'A', 'state' => 1], 'title');
        $this->writer->upsert(
            'ww_upsert_items',
            ['title' => 'A', 'state' => null],
            'title',
            options: ['updateNulls' => false]
        );

        self::assertEquals(1, $this->fetchRows()[0]['state']);
    }

    #[Test]
    public function upsertAcceptsObjects(): void
    {
        $data = (object) ['title' => 'O', 'state' => 3];

        $this->writer->upsert('ww_upsert_items', $data, 'title');

        self::assertEquals(3, $this->fetchRows()[0]['state']);
    }

    #[Test]
    public function upsertReturnsIdField(): void
    {
        $data = $this->writer->upsert(
            'ww_upsert_items',
            ['title' => 'B', 'state' => 1],
            'title',
            options: ['returningIdField' => 'id']
        );

        self::assertNotEmpty($data['id']);

        $again = $this->writer->upsert(
            'ww_upsert_items',
            ['title' => 'B', 'state' => 2],
            'title',
            options: ['returningIdField' => 'id']
        );

        self::assertEquals($data['id'], $again['id']);
    }
}
