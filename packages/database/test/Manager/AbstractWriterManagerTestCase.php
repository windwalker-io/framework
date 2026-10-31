<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\WriterManager;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

use function Windwalker\raw;

abstract class AbstractWriterManagerTestCase extends AbstractDatabaseTestCase
{
    protected WriterManager $writer;

    /**
     * Should create `writer_items(id auto increment PK, title unique NOT NULL, state int NULL, params text NULL)`.
     */
    abstract protected static function createWriterTable(): void;

    /**
     * Should empty the table and restart the auto increment from 1.
     */
    abstract protected static function resetWriterTableData(): void;

    #[Test]
    public function insertOneSetsIncrementedKeyOnArrayAndObject(): void
    {
        $data = $this->writer->insertOne('writer_items', ['title' => 'A', 'state' => 1], 'id');

        self::assertSame('1', (string) $data['id']);

        $object = $this->writer->insertOne('writer_items', (object) ['title' => 'B', 'state' => 2], 'id');

        self::assertSame('2', (string) $object->id);
        self::assertSame(2, $this->countRows());
    }

    #[Test]
    public function insertOneCanFilterUnknownFields(): void
    {
        $this->writer->insertOne(
            'writer_items',
            ['title' => 'A', 'no_such_column' => 'x'],
            null,
            ['filterFields' => true]
        );

        self::assertSame(1, $this->countRows());
        self::assertSame(1, $this->writer->countAffected());
    }

    #[Test]
    public function updateOneUpdatesByKeyAndRespectsUpdateNulls(): void
    {
        $this->seed();

        $this->writer->updateOne(
            'writer_items',
            ['id' => 1, 'title' => 'Changed', 'state' => null],
            'id',
            ['updateNulls' => false, 'toJson' => false]
        );

        $row = $this->row(1);

        self::assertSame('Changed', $row['title']);
        self::assertSame('1', (string) $row['state']);
    }

    #[Test]
    public function updateOneSerializesArraysToJsonOnlyWhenRequested(): void
    {
        $this->seed();

        $this->writer->updateOne(
            'writer_items',
            ['id' => 1, 'params' => ['a' => 1]],
            'id',
            ['toJson' => true]
        );

        self::assertSame('{"a":1}', $this->row(1)['params']);

        $this->writer->updateOne(
            'writer_items',
            ['id' => 1, 'title' => 'Skip', 'params' => ['b' => 2]],
            'id',
            ['toJson' => false]
        );

        self::assertSame('{"a":1}', $this->row(1)['params']);
        self::assertSame('Skip', $this->row(1)['title']);
    }

    #[Test]
    public function updateOneSupportsFilterFieldsAndLockCallback(): void
    {
        $this->seed();

        $called = false;

        $this->writer->updateOne(
            'writer_items',
            ['id' => 2, 'title' => 'Filtered', 'no_such_column' => 'x'],
            'id',
            [
                'filterFields' => ['id', 'title'],
                'toJson' => false,
                'lockCallback' => function () use (&$called) {
                    $called = true;
                },
            ]
        );

        self::assertTrue($called);
        self::assertSame('Filtered', $this->row(2)['title']);
    }

    #[Test]
    public function updateOneRejectsEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->writer->updateOne('writer_items', ['id' => 1], []);
    }

    #[Test]
    public function saveOneInsertsWithoutKeyValueAndUpdatesWithIt(): void
    {
        $this->writer->saveOne('writer_items', ['title' => 'New', 'state' => 1], 'id');

        self::assertSame(1, $this->countRows());

        $this->writer->saveOne('writer_items', ['id' => 1, 'title' => 'Saved'], 'id', ['toJson' => false]);

        self::assertSame(1, $this->countRows());
        self::assertSame('Saved', $this->row(1)['title']);

        $this->writer->saveOne('writer_items', (object) ['id' => 1, 'title' => 'Obj'], 'id', ['toJson' => false]);

        self::assertSame('Obj', $this->row(1)['title']);
    }

    #[Test]
    public function upsertInsertsThenUpdatesOnDuplicateKey(): void
    {
        $result = $this->writer->upsert(
            'writer_items',
            ['title' => 'Unique', 'state' => 1],
            'title',
            null,
            ['returningIdField' => 'id']
        );

        self::assertSame(1, (int) $result['id']);

        $this->writer->upsert(
            'writer_items',
            ['title' => 'Unique', 'state' => 5, 'params' => raw("'raw'")],
            'title',
            ['state', 'params']
        );

        self::assertSame(1, $this->countRows());
        self::assertSame('5', (string) $this->row(1)['state']);
        self::assertSame('raw', $this->row(1)['params']);
    }

    #[Test]
    public function upsertCanSkipNullValues(): void
    {
        $this->writer->upsert('writer_items', ['title' => 'U', 'state' => 3, 'params' => 'keep'], 'title');
        $this->writer->upsert(
            'writer_items',
            ['title' => 'U', 'state' => 4, 'params' => null],
            'title',
            null,
            ['updateNulls' => false]
        );

        self::assertSame('4', (string) $this->row(1)['state']);
        self::assertSame('keep', $this->row(1)['params']);
    }

    #[Test]
    public function insertMultipleAndSaveMultipleHandleEachItem(): void
    {
        $items = $this->writer->insertMultiple(
            'writer_items',
            [['title' => 'A'], ['title' => 'B']],
            'id'
        );

        self::assertCount(2, $items);
        self::assertSame(2, $this->countRows());

        $this->writer->saveMultiple(
            'writer_items',
            [['id' => 1, 'title' => 'A2'], ['title' => 'C']],
            'id',
            ['toJson' => false]
        );

        self::assertSame(3, $this->countRows());
        self::assertSame('A2', $this->row(1)['title']);
    }

    #[Test]
    public function insertBulkInsertsAllItemsInOneStatement(): void
    {
        $statement = $this->writer->insertBulk(
            'writer_items',
            [
                ['title' => 'A', 'state' => 1, 'no_such_column' => 'x'],
                ['title' => 'B', 'state' => 2, 'no_such_column' => 'y'],
                ['title' => 'C', 'state' => 3, 'no_such_column' => 'z'],
            ],
            ['filterFields' => true]
        );

        self::assertSame(3, $statement->countAffected());
        self::assertSame(3, $this->countRows());
    }

    #[Test]
    public function updateMultipleUpdatesEachItem(): void
    {
        $this->seed();

        $statements = $this->writer->updateMultiple(
            'writer_items',
            [['id' => 1, 'title' => 'X'], ['id' => 2, 'title' => 'Y']],
            'id',
            ['toJson' => false]
        );

        self::assertCount(2, $statements);
        self::assertSame('X', $this->row(1)['title']);
        self::assertSame('Y', $this->row(2)['title']);
    }

    #[Test]
    public function updateWhereUpdatesMatchingRowsOrReturnsNullWithoutData(): void
    {
        $this->seed();

        $this->writer->updateWhere(
            'writer_items',
            ['state' => 9, 'no_such_column' => 'x'],
            ['state' => 1],
            ['filterFields' => true]
        );

        self::assertSame(1, $this->writer->countAffected());
        self::assertSame('9', (string) $this->row(1)['state']);

        self::assertNull($this->writer->updateWhere('writer_items', [], ['state' => 1]));
    }

    #[Test]
    public function deleteRemovesRowsByConditions(): void
    {
        $this->seed();

        $this->writer->delete('writer_items', ['id' => 1]);

        self::assertSame(1, $this->writer->countAffected());
        self::assertSame(1, $this->countRows());

        $this->writer->delete('writer_items');

        self::assertSame(0, $this->countRows());
    }

    #[Test]
    public function statementAccessorsTrackLastExecution(): void
    {
        self::assertNull($this->writer->setStatement(null)->getStatement());
        self::assertSame(0, $this->writer->countAffected());
        self::assertNull($this->writer->lastInsertId());
        self::assertSame(self::$db, $this->writer->getDb());

        $statement = $this->writer->execute('SELECT 1');

        self::assertSame($statement, $this->writer->getStatement());
    }

    #[Test]
    public function filterFieldsKeepsOnlyExistingColumns(): void
    {
        self::assertSame(
            ['title' => 'A'],
            $this->writer->filterFields('writer_items', ['title' => 'A', 'foo' => 'B'])
        );
    }

    protected static function setupDatabase(): void
    {
        static::createWriterTable();
    }

    #[Before]
    protected function resetWriterTable(): void
    {
        static::resetWriterTableData();

        $this->writer = self::$db->getWriter(true);
    }

    protected function seed(): void
    {
        $this->writer->insertBulk(
            'writer_items',
            [
                ['title' => 'First', 'state' => 1],
                ['title' => 'Second', 'state' => 2],
            ]
        );
    }

    protected function countRows(): int
    {
        return (int) self::$baseConn->query('SELECT COUNT(*) FROM writer_items')->fetchColumn();
    }

    protected function row(int $id): array
    {
        return self::$baseConn->query("SELECT * FROM writer_items WHERE id = $id")
            ->fetch(\PDO::FETCH_ASSOC);
    }
}
