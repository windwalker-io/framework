> Draft: to be merged into the official docs, then delete this file.

## Cursor Pagination for a Single Query (`withCursor()`)

`withCursor()` returns a new query that is sorted by the cursor columns and has a keyset condition that starts right
after the last row the client has already seen. Use it in APIs that return one page per request and
receive the last row (or its values) back from the client. Unlike offset pagination, the database does not scan the
skipped rows, and new rows inserted before the cursor do not shift the pages.

`limit()` keeps its normal meaning, so the page size is set with the native `limit()` method.

### Basic Usage

Pass the column, direction and the last seen value:

```php
$query = $db->createQuery()
    ->select('*')
    ->from('articles')
    ->where('state', 1)
    ->order('id', 'DESC')
    ->limit(20);

if ($lastId !== null) {
    $query = $query->withCursor('id', 'DESC', $lastId);
}

$items = $query->all();
```

```sql
SELECT * FROM "articles" WHERE "state" = ? AND "id" < ? ORDER BY "id" DESC LIMIT 20
```

Keep the `order()` of the first page identical to the cursor order. `withCursor()` replaces the existing `ORDER BY`
with the cursor columns, so the first page and later pages are always sorted the same way.

### Multiple Columns

Pass a list of `[column, direction, value]` tuples. The first column is the primary sort key and the list is one
composite cursor:

```php
$query = $query->withCursor([
]);
```

```sql
WHERE (("created" < ?) OR ("created" = ? AND "id" < ?)) ORDER BY "created" DESC, "id" DESC
```

A value can also be a raw expression, which compares against another column:

```php
$query = $query->withCursor([
]);
```

### Using a `CursorPaginate` Definition

When the same sort order is used in several places, define it once with `Query::cursorPaginate()` and pass the last
row or the cursor values keyed by column name:

```php
use Windwalker\Query\Query;

$paginate = Query::cursorPaginate(20, ['created' => 'DESC', 'id' => 'DESC']);

$query = $db->createQuery()
    ->select('*')
    ->from('articles')
    ->where('state', 1);

// First page: $cursor is null. Next pages: $cursor is the last row or its values.
$query = $query->withCursor($paginate, $cursor);

$items = $query->all();
```

When the list comes from a request, pass the values as an array. The `CursorPaginate` form reads each column from the
array key or object property that matches its column name. Table aliases are removed, so `'a.id'` reads `id`.

### Options and Behaviors

- **Direction**: `'ASC'` or `'DESC'` (case-insensitive), or a `\SortDirection` enum case.
- **Returns a new query**: the original query is not changed, so the result must be assigned or used directly.
- **Conditions are combined with `AND`**: they are added to the existing `WHERE` clause.
- **Replaces the order**: the existing `ORDER BY` is cleared and replaced by the cursor columns.
- **Call it once**: each call appends another keyset condition, so use one call per query.
- **No null values**: a `null` value throws `InvalidArgumentException`. Nullable columns are not supported by keyset
  pagination.
- **Closure cursors are not supported with `CursorPaginate`**: a `CursorPaginate` built from a `Closure` cannot read
  values from an item, so passing it to `withCursor()` throws `InvalidArgumentException`.
- **Argument checks**: the string form expects exactly a direction and a value. The `CursorPaginate` form expects
  exactly one item or values array.
- **Ordering must be unique**: put a unique column (such as the primary key) last, otherwise rows with equal cursor
  values can be skipped or repeated.
