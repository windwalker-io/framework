<?php

declare(strict_types=1);

namespace Windwalker\Database\Test;

use BadMethodCallException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\WriterManager;
use Windwalker\ORM\ORM;

class DatabaseAdapterTest extends AbstractDatabaseTestCase
{
    #[Test]
    public function executeAndPrepareTrackQuery(): void
    {
        $stmt = self::$db->execute('SELECT 1');

        self::assertSame('SELECT 1', self::$db->getQuery());
        self::assertNotNull($stmt);
        self::assertSame('SELECT 1', (string) self::$db->getLastQuery());
        self::assertNotSame(self::$db->getQuery(), self::$db->getQuery(true));
    }

    #[Test]
    public function metaAccessors(): void
    {
        self::assertNotEmpty(self::$db->getDriverName());
        self::assertSame(self::$db->getDriver()->getOptions(), self::$db->getOptions());
        self::assertNotEmpty(self::$db->listSchemas());
        self::assertTrue(self::$db->ping());
        self::assertStringNotContainsString('#__', self::$db->replacePrefix('SELECT * FROM #__flower'));
        self::assertSame(self::$db->getTableManager('ww_flower'), self::$db->getTable('ww_flower'));
        self::assertIsArray(self::$db->getTables());
        self::assertIsArray(self::$db->listTables());
        self::assertInstanceOf(WriterManager::class, self::$db->getWriter());
        self::assertSame(self::$db->getWriter(), self::$db->getWriter());
        self::assertNotNull(self::$db->getHydrator());
    }

    #[Test]
    public function transactionStateAndResult(): void
    {
        self::assertFalse(self::$db->isInTransaction());

        $result = self::$db->transaction(function () {
            self::assertTrue(self::$db->isInTransaction());

            return 'done';
        });

        self::assertSame('done', $result);
    }

    #[Test]
    public function countWithQueryAndString(): void
    {
        $total = (int) self::$db->prepare('SELECT COUNT(*) FROM ww_flower')->result();

        $q = self::$db->createQuery()->select('*')->from('ww_flower')->order('id');
        self::assertSame($total, self::$db->countWith($q));

        $grouped = self::$db->createQuery()->select('catid')->from('ww_flower')->group('catid');
        $groups = count(self::$db->prepare($grouped)->all());
        self::assertSame($groups, self::$db->countWith($grouped));

        $str = self::$db->createQuery()->select('*')->from('ww_flower');
        self::assertSame($total, self::$db->countWith($str));
    }

    #[Test]
    public function nullDates(): void
    {
        $nil = self::$db->getNullDate();

        self::assertNotEmpty(self::$db->getDateFormat());
        self::assertTrue(self::$db->isNullDate(null));
        self::assertTrue(self::$db->isNullDate(''));
        self::assertTrue(self::$db->isNullDate($nil));
        self::assertTrue(self::$db->isNullDate('0000-00-00 00:00:00'));
        self::assertFalse(self::$db->isNullDate('2020-01-01 00:00:00'));
        self::assertFalse(self::$db->isNullDate(new DateTimeImmutable('2020-01-01')));
    }

    #[Test]
    public function magicCallDelegatesToQuery(): void
    {
        self::assertSame('select', self::$db->select('id')->getType());
        self::assertSame('delete', self::$db->delete('ww_flower')->getType());
    }

    #[Test]
    public function magicCallRejectsUnknownMethod(): void
    {
        $this->expectException(BadMethodCallException::class);

        self::$db->notExists();
    }

    #[Test]
    public function ormAccessors(): void
    {
        $orm = self::$db->orm();

        self::assertInstanceOf(ORM::class, $orm);
        self::assertSame($orm, self::$db->orm());

        self::$db->setORM(null);
        self::assertNotSame($orm, self::$db->orm());
        self::$db->setORM($orm);
    }

    protected static function setupDatabase(): void
    {
        static::$baseConn->exec('DROP TABLE IF EXISTS ww_flower');
        static::$baseConn->exec('CREATE TABLE ww_flower (id INT PRIMARY KEY, catid INT)');
        static::$baseConn->exec('INSERT INTO ww_flower VALUES (1,1),(2,1),(3,2)');
    }
}
