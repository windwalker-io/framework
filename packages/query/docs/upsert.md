> Draft: to be merged into the official docs, then delete this file.

## Upsert

`upsert()` inserts a row, or updates the existing row when a conflict occurs. The Grammar renders the statement for
the current platform, so the same code works on every supported database:

| Platform   | Rendered as                                |
|------------|--------------------------------------------|
| MySQL      | `INSERT ... ON DUPLICATE KEY UPDATE`       |
| PostgreSQL | `INSERT ... ON CONFLICT (...) DO UPDATE`   |
| SQLite     | `INSERT ... ON CONFLICT (...) DO UPDATE`   |
| SQL Server | `MERGE ... WITH (HOLDLOCK)`                |
| Oracle     | `MERGE INTO ... USING DUAL`                |

A grammar without upsert support throws `BadMethodCallException`.

### Basic Usage

Provide the row with `set()`, or with `columns()` + `values()`, then declare the conflict target with
`onConflict()`.

```php
$db->createQuery()
    ->upsert('cache_items')
    ->set([
        'key' => 'foo',
        'group' => 'bar',
        'payload' => $payload,
    ])
    ->onConflict('key', 'group')
    ->execute();
```

```php
$db->createQuery()
    ->upsert('cache_items')
    ->columns('key', 'group', 'payload')
    ->values(['foo', 'bar', $payload])
    ->onConflict(['key', 'group'])
    ->execute();
```

Only one row can be upserted at a time (with `values()`, only the first row is used). Every conflict key must exist in the row data, otherwise an
`InvalidArgumentException` is thrown. Using `insert()->from(...)` data (INSERT ... SELECT) is not supported.

### onConflict()

`onConflict()` accepts column names as arguments or as an array. It can be called multiple times, and the keys are
accumulated without duplicates.

MySQL ignores the keys because it relies on the table's unique indexes. SQL Server and Oracle use them as the
`MERGE ... ON` condition and throw `InvalidArgumentException` when no key is set. PostgreSQL and SQLite require them to match a unique index.

### onConflictUpdateColumns() / onConflictUpdateValues()

`onConflictUpdateColumns()` updates columns with the inserted values, and `onConflictUpdateValues()` updates columns
with custom values. Both can be called multiple times and accumulate.

```php
use function Windwalker\raw;

$query->upsert('articles')
    ->set(['id' => 1, 'title' => 'Hello', 'hits' => 0])
    ->onConflict('id')
    // Update with the inserted value
    ->onConflictUpdateColumns('title', 'state')
    // Update with a custom value
    ->onConflictUpdateValues('hits', raw('hits + 1'));
```

`onConflictUpdateValues()` also accepts a `[column => value]` map:

```php
$query->onConflictUpdateValues(['hits' => raw('hits + 1'), 'state' => 1]);
```

Custom values follow the same rules as `set()`: scalars are bound as parameters, `raw()` is rendered as is, and a
`Closure` receives a sub-query.

If neither method is called, all inserted columns except the conflict keys are updated. If there are no
other columns, the conflict keys themselves are updated.

### returning()

`returning()` sets the ID column to get back after the upsert.

```php
$statement = $query->upsert('articles', incrementField: true)
    ->set(['slug' => 'hello', 'title' => 'Hello'])
    ->onConflict('slug')
    ->returning('id')
    ->execute();
```

- **MySQL**: adds `id = LAST_INSERT_ID(id)`, so read it with `$statement->lastInsertId()`.
- **PostgreSQL / SQLite**: adds `RETURNING id`, so read it from the result set.
- **SQL Server**: adds `OUTPUT INSERTED.id`.
- **Oracle**: `MERGE` cannot return the ID, so `returning()` is ignored. Query the row by its conflict keys instead.

### Notes

- Each `upsert()` call resets the upsert state, including keys, update columns, and `returning()`.
- On SQL Server and Oracle the same value appears in several places of the `MERGE` statement, so it is bound once per
  place. This keeps the statement valid with drivers that do not support repeated named placeholders.
