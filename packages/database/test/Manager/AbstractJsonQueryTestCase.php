<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * Runs the Query JSON helpers (cast / exists / set / remove) against a real database.
 */
abstract class AbstractJsonQueryTestCase extends AbstractDatabaseTestCase
{
    /**
     * Should create `ww_json_items(id int PK, data <json column>)`.
     */
    abstract protected static function createJsonTable(): void;

    protected static function setupDatabase(): void
    {
        static::createJsonTable();
    }

    #[Before]
    protected function prepareRows(): void
    {
        self::$baseConn->exec('DELETE FROM ww_json_items');

        $rows = [
            1 => '{"n": 5, "s": "abc", "d": "2024-01-02", "o": {"x": 1}}',
            2 => '{"n": 20, "s": "xyz", "d": "2025-06-07"}',
            3 => '{"n": 100, "s": "mmm"}',
        ];

        foreach ($rows as $id => $json) {
            self::$baseConn->exec(
                sprintf("INSERT INTO ww_json_items (id, data) VALUES (%d, '%s')", $id, $json)
            );
        }
    }

    protected function ids(\Windwalker\Query\Query $query): array
    {
        $ids = self::$db->prepare($query)->loadColumn()->dump();

        $ids = array_map('intval', $ids);
        sort($ids);

        return $ids;
    }

    protected function select(): \Windwalker\Query\Query
    {
        return self::$db->createQuery()->select('id')->from('ww_json_items');
    }

    #[Test]
    public function castNumericComparison(): void
    {
        // String comparison would put '100' before '20'; a real cast must not.
        self::assertSame([2, 3], $this->ids($this->select()->where('data->n::int', '>', 10)));
    }

    #[Test]
    public function castOrder(): void
    {
        $query = $this->select()->order('data->n::int', 'DESC');

        $ids = array_map('intval', self::$db->prepare($query)->loadColumn()->dump());

        self::assertSame([3, 2, 1], $ids);
    }

    #[Test]
    public function castString(): void
    {
        self::assertSame([2], $this->ids($this->select()->where('data->s::string', '=', 'xyz')));
    }

    #[Test]
    public function castDate(): void
    {
        self::assertSame(
            [2],
            $this->ids($this->select()->where('data->d::date', '>', '2025-01-01'))
        );
    }

    #[Test]
    public function exists(): void
    {
        self::assertSame([1], $this->ids($this->select()->whereJsonExists('data->o->x')));
        self::assertSame([2, 3], $this->ids($this->select()->whereJsonNotExists('data->o->x')));
    }

    #[Test]
    public function setJsonUpdatesValue(): void
    {
        self::$db->execute(
            self::$db->createQuery()
                ->update('ww_json_items')
                ->setJson('data->n', 999)
                ->where('id', 1)
        );

        self::assertSame([1, 3], $this->ids($this->select()->where('data->n::int', '>', 50)));
        self::assertSame([1], $this->ids($this->select()->where('data->n::int', '=', 999)));
    }

    #[Test]
    public function setJsonStringValue(): void
    {
        self::$db->execute(
            self::$db->createQuery()
                ->update('ww_json_items')
                ->setJson('data->s', "it's \"x\"")
                ->where('id', 1)
        );

        self::assertSame([1], $this->ids($this->select()->where('data->s::string', '=', "it's \"x\"")));
    }

    #[Test]
    public function setJsonRemoveDeletesKey(): void
    {
        self::$db->execute(
            self::$db->createQuery()
                ->update('ww_json_items')
                ->setJsonRemove('data->o->x')
                ->where('id', 1)
        );

        self::assertSame([], $this->ids($this->select()->whereJsonExists('data->o->x')));
    }
}
