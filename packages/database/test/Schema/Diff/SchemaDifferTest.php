<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema\Diff;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;
use Windwalker\Database\Schema\Diff\SchemaDiffer;
use Windwalker\Database\Schema\Schema;

class SchemaDifferTest extends TestCase
{
    private function createDiffer(): SchemaDiffer
    {
        $platform = $this->createStub(AbstractPlatform::class);
        $platform->method('prepareColumn')->willReturnArgument(0);

        return new SchemaDiffer($platform);
    }

    private function createSchema(): Schema
    {
        return new Schema($this->createStub(TableManager::class));
    }

    #[Test]
    public function columnDiffDetectsChangedProperties(): void
    {
        $desired = new Column('title', 'varchar(255)', false, '');
        $actual = new Column('title', 'varchar(100)', true, '');

        $changes = $desired->diff($actual);

        self::assertSame(['length', 'nullable'], array_keys($changes));
        self::assertSame(['from' => '100', 'to' => '255'], $changes['length']);
        self::assertSame([], $desired->diff(clone $desired));
    }

    #[Test]
    public function columnDiffIgnoresUndefinedOptionalProperties(): void
    {
        $desired = new Column('title', 'varchar(255)', false, '');
        $actual = new Column('title', 'varchar(255)', false, '')
            ->comment('Title')
            ->collation('utf8mb4_general_ci');

        self::assertSame([], $desired->diff($actual));
        self::assertSame(['comment'], array_keys((clone $desired)->comment('Other')->diff($actual)));
    }

    #[Test]
    public function diffColumns(): void
    {
        $schema = $this->createSchema();
        $schema->addColumn(new Column('id', 'int', false, 0));
        $schema->addColumn(new Column('title', 'varchar(255)', false, ''));
        $schema->addColumn(new Column('created', 'datetime', true));

        $diff = $this->createDiffer()->diff(
            'articles',
            $schema,
            [
                'id' => new Column('id', 'int', false, 0),
                'title' => new Column('title', 'varchar(100)', false, ''),
                'legacy' => new Column('legacy', 'int', false, 0),
            ],
            dropMissing: true,
        );

        self::assertFalse($diff->isEmpty());
        self::assertSame(['created'], array_keys($diff->addedColumns));
        self::assertSame(['title'], array_keys($diff->modifiedColumns));
        self::assertSame(['length'], array_keys($diff->getColumnChanges()['title']));
        self::assertSame(['legacy'], $diff->droppedColumns);
    }

    #[Test]
    public function missingColumnsAreKeptByDefault(): void
    {
        $diff = $this->createDiffer()->diff(
            'articles',
            $this->createSchema(),
            ['legacy' => new Column('legacy', 'int', false, 0)],
        );

        self::assertTrue($diff->isEmpty());
    }

    #[Test]
    public function diffIndexesAndConstraints(): void
    {
        $schema = $this->createSchema();
        $schema->addIndex(['title'], 'idx_title');
        $schema->addIndex(['alias'], 'idx_alias');
        $schema->addUniqueKey(['alias'], 'uq_alias');

        $actualIndexes = [
            'idx_title' => new Index('idx_title')->columns(['title']),
            'idx_alias' => new Index('idx_alias')->columns(['alias', 'title']),
            'idx_old' => new Index('idx_old')->columns(['old']),
        ];
        $actualConstraints = [
            'uq_alias' => new Constraint(Constraint::TYPE_UNIQUE, 'uq_alias')->columns(['alias']),
            'PRIMARY' => new Constraint(Constraint::TYPE_PRIMARY_KEY, 'PRIMARY')->columns(['id']),
        ];

        $diff = $this->createDiffer()->diff('articles', $schema, [], $actualIndexes, $actualConstraints, true);

        self::assertSame([], array_keys($diff->addedIndexes));
        self::assertSame(['idx_alias'], array_keys($diff->modifiedIndexes));
        self::assertSame(['idx_old'], $diff->droppedIndexes);
        self::assertSame([], array_keys($diff->addedConstraints));
        self::assertSame([], array_keys($diff->modifiedConstraints));
        self::assertSame([], $diff->droppedConstraints);
    }
}
