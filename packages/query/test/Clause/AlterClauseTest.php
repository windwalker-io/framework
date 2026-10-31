<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Clause;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;

class AlterClauseTest extends TestCase
{
    #[Test]
    public function renderBuildsAlterTableAddIndex(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo');
        $alter->addIndex('idx_sakura', ['id', 'sakura']);

        self::assertEquals("ALTER TABLE \"foo\"\nADD INDEX \"idx_sakura\" (id,sakura)", (string) $alter);
    }

    #[Test]
    public function constraintHelpersAppendSubClauses(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo');
        $alter->addPrimaryKey('pk', ['id']);
        $alter->addUniqueKey('u', ['a', 'b']);
        $alter->addConstraint(null, 'CHECK', ['x']);

        self::assertEquals(
            "ALTER TABLE \"foo\"\nADD CONSTRAINT \"pk\" PRIMARY KEY (id),\n"
            . "ADD CONSTRAINT \"u\" UNIQUE (a,b),\nADD CONSTRAINT CHECK (x)",
            (string) $alter
        );
    }

    #[Test]
    public function addForeignKeyAppendsReferencesAndActions(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo');
        $alter->addForeignKey('fk', ['uid'], ['id'], 'CASCADE', 'SET NULL');

        self::assertEquals(
            "ALTER TABLE \"foo\"\nADD CONSTRAINT \"fk\" FOREIGN KEY (uid) REFERENCES (id)"
            . ' ON UPDATE CASCADE ON DELETE SET NULL',
            (string) $alter
        );
    }

    #[Test]
    public function addForeignKeyOmitsEmptyActions(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo');
        $alter->addForeignKey('fk', ['uid'], ['id'], null, null);

        self::assertEquals(
            "ALTER TABLE \"foo\"\nADD CONSTRAINT \"fk\" FOREIGN KEY (uid) REFERENCES (id)",
            (string) $alter
        );
    }

    #[Test]
    public function modifyColumnAppendsModifyColumnClause(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo');
        $alter->modifyColumn('c', 'INT');

        self::assertEquals("ALTER TABLE \"foo\"\nMODIFY COLUMN \"c\" INT", (string) $alter);
    }

    #[Test]
    public function appendAddsRawElements(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo')->append('DROP COLUMN bar');

        self::assertEquals("ALTER TABLE \"foo\"\nDROP COLUMN bar", (string) $alter);
    }

    public static function createQuery($conn = null): Query
    {
        return new Query($conn ?: new MockEscaper(), new BaseGrammar());
    }

    #[Test]
    public function targetHelpersQuoteNamesOnce(): void
    {
        $alter = self::createQuery()->alter('TABLE', 'foo');

        self::assertEquals('ALTER TABLE "foo"."bar"', trim((string) $alter->table('foo.bar')));
        self::assertEquals('ALTER DATABASE "d"', trim((string) $alter->database('d')));
        self::assertEquals('ALTER SCHEMA "s"', trim((string) $alter->schema('s')));
    }
}
