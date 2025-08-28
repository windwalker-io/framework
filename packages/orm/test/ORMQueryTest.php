<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use Windwalker\Database\Event\QueryStartEvent;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\ORM;

use function PHPUnit\Framework\assertEquals;

class ORMQueryTest extends AbstractDatabaseTestCase
{
    protected ORM $orm {
        get => self::$db->orm();
    }

    public function testPaginatedIterator(): void
    {
        $queries = [];

        $this->orm->getDb()->on(
            QueryStartEvent::class,
            function (QueryStartEvent $event) use (&$queries) {
                $queries[] = $event->debugQueryString;
            }
        );

        $items = $this->orm->from('ww_flower')
            ->getIterator(paginate: 10);

        $ids = [];

        foreach ($items as $item) {
            $ids[] = $item->id;
        }

        assertEquals(
            expected: [
                'SELECT * FROM `ww_flower` LIMIT 0, 10',
                'SELECT * FROM `ww_flower` LIMIT 10, 10',
                'SELECT * FROM `ww_flower` LIMIT 20, 10',
                'SELECT * FROM `ww_flower` LIMIT 30, 10',
                'SELECT * FROM `ww_flower` LIMIT 40, 10',
                'SELECT * FROM `ww_flower` LIMIT 50, 10',
                'SELECT * FROM `ww_flower` LIMIT 60, 10',
                'SELECT * FROM `ww_flower` LIMIT 70, 10',
                'SELECT * FROM `ww_flower` LIMIT 80, 10',
                'SELECT * FROM `ww_flower` LIMIT 90, 10',
            ],
            actual: $queries
        );

        assertEquals(85, count($ids));
    }

    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/data.sql');
    }
}
