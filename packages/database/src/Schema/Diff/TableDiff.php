<?php

declare(strict_types=1);

namespace Windwalker\Database\Schema\Diff;

use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;

/**
 * The differences between a desired Schema and the actual table.
 *
 * Modified indexes / constraints hold the desired definition, they must be dropped and re-created to apply.
 */
class TableDiff
{
    /**
     * @param  array<string, Column>      $addedColumns
     * @param  array<string, ColumnDiff>  $modifiedColumns
     * @param  string[]                   $droppedColumns
     * @param  array<string, Index>       $addedIndexes
     * @param  array<string, Index>       $modifiedIndexes
     * @param  string[]                   $droppedIndexes
     * @param  array<string, Constraint>  $addedConstraints
     * @param  array<string, Constraint>  $modifiedConstraints
     * @param  string[]                   $droppedConstraints
     */
    public function __construct(
        public readonly string $table,
        public readonly array $addedColumns = [],
        public readonly array $modifiedColumns = [],
        public readonly array $droppedColumns = [],
        public readonly array $addedIndexes = [],
        public readonly array $modifiedIndexes = [],
        public readonly array $droppedIndexes = [],
        public readonly array $addedConstraints = [],
        public readonly array $modifiedConstraints = [],
        public readonly array $droppedConstraints = [],
    ) {}

    public function isEmpty(): bool
    {
        return $this->addedColumns === []
            && $this->modifiedColumns === []
            && $this->droppedColumns === []
            && $this->addedIndexes === []
            && $this->modifiedIndexes === []
            && $this->droppedIndexes === []
            && $this->addedConstraints === []
            && $this->modifiedConstraints === []
            && $this->droppedConstraints === [];
    }

    /**
     * @return  array<string, array<string, mixed>>  Column name => changed properties, for reporting.
     */
    public function getColumnChanges(): array
    {
        return array_map(static fn(ColumnDiff $diff) => $diff->changes, $this->modifiedColumns);
    }
}
