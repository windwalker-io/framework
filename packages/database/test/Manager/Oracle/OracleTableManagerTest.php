<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\Oracle;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class OracleTableManagerTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driver = 'pdo_oci';

    #[Test]
    public function createBuildsTableWithColumnsAndKeys(): void
    {
        $table = self::$db->getTableManager('ww_oracle_ent');

        $table->create(
            static function (Schema $schema) {
                $schema->primary('id');
                $schema->char('type')->length(25);
                $schema->integer('catid')->nullable(true);
                $schema->varchar('alias');
                $schema->varchar('title')->defaultValue('H');
                $schema->decimal('price')->length('20,6');
                $schema->text('intro');
                $schema->datetime('created')->nullable(true);
                $schema->timestamp('updated')->nullable(true);
                $schema->json('params')->nullable(true);

                $schema->addIndex(['catid', 'type']);
                $schema->addUniqueKey('alias');
            }
        );

        self::assertTrue($table->exists());

        $columns = $table->getColumns(true);

        self::assertEqualsCanonicalizing(
            ['id', 'type', 'catid', 'alias', 'title', 'price', 'intro', 'created', 'updated', 'params'],
            array_keys($columns)
        );
        self::assertTrue($table->hasColumn('title'));
        self::assertFalse($table->hasColumn('nope'));
        self::assertTrue($table->hasIndex('idx_ww_oracle_ent_catid_type'));
        self::assertNotEmpty($table->getConstraints(true));
    }

    #[Test]
    public function addModifyAndDropColumn(): void
    {
        $table = self::$db->getTableManager('ww_oracle_ent');

        $table->addColumn('extra', 'varchar', true, null, ['length' => 100]);
        $table->reset();

        self::assertTrue($table->hasColumn('extra'));

        $table->modifyColumn('extra', 'varchar', true, null, ['length' => 200]);
        $table->reset();

        self::assertEquals(200, $table->getColumn('extra')->getCharacterMaximumLength());

        $table->dropColumn('extra');
        $table->reset();

        self::assertFalse($table->hasColumn('extra'));
    }

    #[Test]
    public function dateTimeStringsAreAcceptedForDateAndTimestampColumns(): void
    {
        self::$db->getWriter()->insertOne(
            'ww_oracle_ent',
            [
                'type' => 'dt',
                'alias' => 'dt1',
                'price' => 1,
                'intro' => 'i',
                'created' => '2020-01-02 03:04:05',
                'updated' => '2020-01-02 03:04:05.123',
            ]
        );

        $row = self::$db->select('created', 'updated')
            ->from('ww_oracle_ent')
            ->where('alias', 'dt1')
            ->get();

        self::assertStringStartsWith('2020-01-02 03:04:05', (string) $row['created']);
        self::assertStringStartsWith('2020-01-02 03:04:05', (string) $row['updated']);

        self::$db->delete('ww_oracle_ent')->where('alias', 'dt1')->execute();
    }

    #[Test]
    public function insertTruncateRenameAndDrop(): void
    {
        $table = self::$db->getTableManager('ww_oracle_ent');

        self::$db->getWriter()->insertOne('ww_oracle_ent', ['type' => 'a', 'alias' => 'x1', 'price' => 1, 'intro' => 'i']);

        self::assertEquals(1, self::$db->select()->from('ww_oracle_ent')->count());

        $table->truncate();

        self::assertEquals(0, self::$db->select()->from('ww_oracle_ent')->count());

        $table->rename('ww_oracle_ent2', false);

        self::assertTrue(self::$db->getTableManager('ww_oracle_ent2', true)->exists());
        self::assertFalse(self::$db->getTableManager('ww_oracle_ent', true)->exists());

        self::$db->getTableManager('ww_oracle_ent2', true)->drop();

        self::assertFalse(self::$db->getTableManager('ww_oracle_ent2', true)->exists());
    }

    protected static function setupDatabase(): void
    {
    }
}
