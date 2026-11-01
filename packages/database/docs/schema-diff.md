# Schema Diff & Sync

Compare a desired table structure (a `Schema`) with the actual table, then preview or apply only the necessary
changes. This is the building block for application-level "sync schema" / migration generation.

## Usage

```php
$table = $db->getTableManager('articles');

$define = static function (Schema $schema) {
    $schema->primary('id');
    $schema->varchar('title')->length(255)->defaultValue('');
    $schema->varchar('subtitle')->defaultValue('');
    $schema->addIndex(['title'], 'idx_articles_title');
};

// 1. Preview, nothing is executed
$diff = $table->diff($define);

$diff->isEmpty();
$diff->addedColumns;      // array<string, Column>
$diff->modifiedColumns;   // array<string, ColumnDiff> ($desired, $actual, $changes)
$diff->droppedColumns;    // string[]
$diff->getColumnChanges(); // ['title' => ['length' => ['from' => '100', 'to' => '255']]]

// 2. Apply a diff
$table->applyDiff($diff);

// 3. Or diff + apply in one call (returns the applied TableDiff)
$table->sync($define);
```

`TableDiff` also exposes `addedIndexes`, `modifiedIndexes`, `droppedIndexes` and the same three lists for
constraints.

## Dry-run (SQL preview)

```php
$sql = $table->syncSql($define);     // string[], nothing executed
$sql = $table->getDiffSql($diff);    // same, from an existing TableDiff

// Any DDL flow can be previewed with the adapter
$sql = $db->pretend(fn() => $table->sync($define));
```

`DatabaseAdapter::pretend()` records every write statement passed to `execute()` instead of running it. Read
statements (`SELECT`, `SHOW`, `PRAGMA`, ...) still run so introspection works. Because the table is not changed,
steps that depend on earlier steps being applied (e.g. SQLite table rebuilds) are previewed on a best-effort basis.

## Semantics

- Columns are normalized with `AbstractPlatform::prepareColumn()` before comparing, so type aliases, lengths and
  default values match the platform's representation.
- `Column::diff(Column $actual)` returns changed properties (`dataType`, `length`, `nullable`, `default`, `unsigned`,
  `autoIncrement`, `generation`, `generatedStored`, `comment`, `charset`, `collation`) as `from` (actual) / `to`
  (desired). `length`, `comment`, `charset` and `collation` are only compared when the desired column defines them.
- By default, columns / indexes / constraints that exist in the table but not in the Schema are **kept**; only
  explicit `$schema->dropColumns()` / `dropIndexes()` / `dropConstraints()` are reported. Pass `dropMissing: true`
  to `diff()` / `sync()` to also drop everything not declared.
- Indexes and constraints are matched by name (primary keys by type) and are "modified" when their columns, uniqueness,
  referenced table/columns or update/delete rules differ. Applying a modification drops and re-creates the key.
- Renames are not detected; a renamed column appears as one drop and one add. Use `TableManager::renameColumn()`.

## Cross-database behavior

Verified against MySQL, PostgreSQL, SQL Server and SQLite. To keep the diff free of driver noise, comparison is
normalized:

- Type aliases are equal (`numeric`/`decimal`, `integer`/`int`, `bool`/`boolean`, `character varying`/`varchar`).
- Integer display width is ignored (`int(10)` vs `int(11)`).
- Numeric defaults compare by value (`0.000000` equals `0`).

## Limitations

- `applyDiff()` uses the existing `TableManager` operations, so platform limits still apply (e.g. SQLite cannot
  modify columns or add constraints to an existing table).
