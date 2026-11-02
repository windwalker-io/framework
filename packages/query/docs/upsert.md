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

## WriterManager and ORM

`WriterManager::upsert()` and `ORM::upsert()` / `EntityMapper::upsert()` accept `$updateFields` as:

- `null`: update all inserted columns except the conflict keys.
- a list `['title', 'state']`: columns updated with the inserted values.
- a map `['hits' => raw('hits + 1')]`: custom values. Integer keys and string keys can be mixed.
- a `Closure` `fn(array $data, array $keys): array`: receives the data and returns a list or a map.

### Conflict detection differs by platform

MySQL cannot choose the conflict keys: `ON DUPLICATE KEY UPDATE` fires on **any** PRIMARY or UNIQUE index violation, so
the keys given to `onConflict()` (or `$condFields` in the ORM) are not part of the SQL. This has two consequences:

- If the keys have no matching PRIMARY/UNIQUE index, MySQL does not match anything and simply inserts a new row.
- If the table has several unique indexes, a row matching any of them is updated, which may not be the row you meant.

The keys still matter: PostgreSQL and SQLite need them to match a unique index, SQL Server and Oracle use them as
the `MERGE ... ON` condition, and `$updateFields` defaults to every column except the keys.

### Safest usage when the database is unknown

To get the same result on every platform:

1. Make the conflict keys a real PRIMARY KEY or UNIQUE index (a single index, with exactly those columns).
2. Pass exactly those columns to `onConflict()` / `$condFields`. Do not rely on other unique indexes to trigger the
   update.
3. Always provide the keys in the data. For auto-increment IDs, either leave the ID out and use a unique business key
   as the conflict key, or only use the ID as the key when it is set.
4. Pass `$updateFields` explicitly instead of relying on the default, and do not include the keys in it.
5. Do not depend on `returning()` alone to read the ID; use the value returned by `WriterManager::upsert()` or the
   ORM, which resolves it per platform.

```php
// articles has UNIQUE (slug)
$db->getWriter()->upsert(
    'articles',
    ['slug' => 'hello', 'title' => 'Hello', 'hits' => 0],
    'slug',
    ['title', 'hits' => raw('hits + 1')],
    ['returningIdField' => 'id']
);

$orm->upsert(Article::class, $article, ['slug'], ['title']);
```
