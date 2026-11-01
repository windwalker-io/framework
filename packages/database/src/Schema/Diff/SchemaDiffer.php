<?php

declare(strict_types=1);

namespace Windwalker\Database\Schema\Diff;

use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;
use Windwalker\Database\Schema\Schema;

/**
 * Compare a desired Schema with the actual table structure.
 */
class SchemaDiffer
{
    public function __construct(protected AbstractPlatform $platform) {}

    /**
     * @param  string             $table
     * @param  Schema             $desired
     * @param  array<Column>      $actualColumns
     * @param  array<Index>       $actualIndexes
     * @param  array<Constraint>  $actualConstraints
     * @param  bool               $dropMissing        Drop columns / keys that exist in the table but not in `$desired`.
     *                                                Explicit drops in the Schema (`dropColumns()` etc.) are always applied.
     *
     * @return  TableDiff
     */
    public function diff(
        string $table,
        Schema $desired,
        array $actualColumns,
        array $actualIndexes = [],
        array $actualConstraints = [],
        bool $dropMissing = false,
    ): TableDiff {
        [$addedColumns, $modifiedColumns, $droppedColumns] = $this->diffColumns(
            $desired,
            $actualColumns,
            $dropMissing
        );

        $indexNames = array_map(static fn(Index $i) => $i->indexName, $desired->getIndexes());
        $constraintNames = array_map(static fn(Constraint $c) => $c->constraintName, $desired->getConstraints());

        [$addedIndexes, $modifiedIndexes, $droppedIndexes] = $this->diffKeys(
            $this->keyBy($desired->getIndexes(), 'indexName'),
            $this->keyBy($actualIndexes, 'indexName'),
            $desired->dropIndexes,
            $dropMissing ? array_diff($this->namesOf($actualIndexes, 'indexName'), $indexNames, $constraintNames) : [],
            fn(Index $d, Index $a) => $this->indexChanged($d, $a),
        );

        [$addedConstraints, $modifiedConstraints, $droppedConstraints] = $this->diffKeys(
            $this->keyConstraints($desired->getConstraints()),
            $this->keyConstraints($actualConstraints),
            $desired->dropConstraints,
            $dropMissing
                ? array_diff(
                    $this->namesOf(
                        array_filter(
                            $actualConstraints,
                            static fn(Constraint $c) => $c->constraintType !== Constraint::TYPE_PRIMARY_KEY
                        ),
                        'constraintName'
                    ),
                    $indexNames,
                    $constraintNames
                )
                : [],
            fn(Constraint $d, Constraint $a) => $this->constraintChanged($d, $a),
        );

        return new TableDiff(
            $table,
            $addedColumns,
            $modifiedColumns,
            $droppedColumns,
            $addedIndexes,
            $modifiedIndexes,
            $droppedIndexes,
            $addedConstraints,
            $modifiedConstraints,
            $droppedConstraints,
        );
    }

    protected function diffColumns(Schema $desired, array $actualColumns, bool $dropMissing): array
    {
        $actual = $this->keyBy($actualColumns, 'columnName');
        $added = [];
        $modified = [];

        foreach ($desired->getColumns() as $column) {
            $column = $this->platform->prepareColumn(clone $column);
            $name = $column->getColumnName();

            if (!isset($actual[$name])) {
                $added[$name] = $column;
                continue;
            }

            $changes = $column->diff($this->platform->prepareColumn(clone $actual[$name]));

            if ($changes !== []) {
                $modified[$name] = new ColumnDiff($column, $actual[$name], $changes);
            }
        }

        $dropped = $desired->dropColumns;

        if ($dropMissing) {
            $dropped = array_merge(
                $dropped,
                array_diff(array_keys($actual), array_keys($this->keyBy($desired->getColumns(), 'columnName')))
            );
        }

        return [$added, $modified, array_values(array_unique(array_intersect($dropped, array_keys($actual))))];
    }

    protected function diffKeys(
        array $desired,
        array $actual,
        array $explicitDrops,
        array $missing,
        callable $changed,
    ): array {
        $added = [];
        $modified = [];

        foreach ($desired as $name => $key) {
            if (!isset($actual[$name])) {
                $added[$name] = $key;
            } elseif ($changed($key, $actual[$name])) {
                $modified[$name] = $key;
            }
        }

        $dropped = array_merge($explicitDrops, $missing);

        return [$added, $modified, array_values(array_unique(array_intersect($dropped, array_keys($actual))))];
    }

    protected function indexChanged(Index $desired, Index $actual): bool
    {
        return $desired->getColumnNames() !== $actual->getColumnNames()
            || (bool) ($desired->isUnique ?? false) !== (bool) ($actual->isUnique ?? false);
    }

    protected function constraintChanged(Constraint $desired, Constraint $actual): bool
    {
        return strtoupper((string) $desired->constraintType) !== strtoupper((string) $actual->constraintType)
            || array_keys($desired->columns) !== array_keys($actual->columns)
            || $desired->referencedTableName !== $actual->referencedTableName
            || array_keys($desired->referencedColumns) !== array_keys($actual->referencedColumns)
            || $this->rule($desired->updateRule) !== $this->rule($actual->updateRule)
            || $this->rule($desired->deleteRule) !== $this->rule($actual->deleteRule);
    }

    protected function rule(?string $rule): string
    {
        $rule = strtoupper((string) $rule);

        return $rule === '' ? 'NO ACTION' : $rule;
    }

    /**
     * @param  array   $items
     * @param  string  $property
     *
     * @return  array<string, mixed>
     */
    protected function keyBy(array $items, string $property): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[(string) $item->$property] = $item;
        }

        return $result;
    }

    /**
     * Primary keys are matched by type since their names differ between platforms.
     *
     * @param  Constraint[]  $constraints
     *
     * @return  array<string, Constraint>
     */
    protected function keyConstraints(array $constraints): array
    {
        $result = [];

        foreach ($constraints as $constraint) {
            $key = strtoupper((string) $constraint->constraintType) === Constraint::TYPE_PRIMARY_KEY
                ? Constraint::TYPE_PRIMARY_KEY
                : (string) $constraint->constraintName;

            $result[$key] = $constraint;
        }

        return $result;
    }

    protected function namesOf(array $items, string $property): array
    {
        return array_keys($this->keyBy($items, $property));
    }
}
