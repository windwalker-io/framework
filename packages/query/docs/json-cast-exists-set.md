> Draft: to be merged into the official docs, then delete this file.

## JSON Cast, Path Existence and Update

These helpers extend the JSON selector syntax (`column->path->>last`) with typed comparison, path existence checks and
partial updates. They work on MySQL, PostgreSQL, SQLite, SQL Server and Oracle; the query compiles to the native
function of each platform.

### Cast Suffix `::type`

Append `::type` after the last path segment to compare or sort the extracted value as a real type instead of text.
Without a cast, `data->n` is extracted as a string on most platforms, so `'100' < '20'`.

```php
$query = $db->createQuery()
    ->select('id', 'data->price::decimal AS price')
    ->from('products')
    ->where('data->stock::int', '>', 10)
    ->order('data->created::datetime', 'DESC');
```

Supported types (case-insensitive):

| Type                    | Meaning              |
|-------------------------|----------------------|
| `int`, `integer`        | Integer              |
| `float`, `double`       | Floating point       |
| `decimal`, `numeric`    | Fixed-point number   |
| `string`, `text`        | Text                 |
| `datetime`              | Date and time        |
| `date`                  | Date                 |

Notes:

- Any other type name throws `InvalidArgumentException`.
- The cast must be the last part of the expression and requires at least one path; `a->b::int->c` throws.
- A cast always extracts the unquoted value, as if the last arrow were `->>`.
- To use a key that literally contains `::`, wrap it in quotes: `data->'a::int'`.
- A plain column with no path (e.g. `where('a::int', ...)`) is treated as an ordinary column name, not a cast.

### Path Existence

`whereJsonExists()` checks that a JSON path is present, even when its value is `null`.

```php
$query = $db->createQuery()
    ->select('*')
    ->from('articles')
    ->whereJsonExists('params->seo->title')
    ->whereJsonNotExists('params->legacy');
```

`havingJsonExists()` and `havingJsonNotExists()` are the `HAVING` counterparts.
To match any of several paths (`OR`), put them inside an `orWhere()` closure, whose conditions are joined by `OR`:

```php
$query->orWhere(fn(Query $q) => $q
    ->whereJsonExists('params->a')
    ->whereJsonExists('params->b')
);
```

### Updating a JSON Value

Use `setJson()` in an `UPDATE` to change one path without rewriting the whole document. The value is JSON-encoded, so
scalars, arrays and objects are all accepted.

```php
$db->execute(
    $db->createQuery()
        ->update('articles')
        ->setJson('params->views', 100)
        ->setJson('params->tags', ['php', 'json'])
        ->where('id', 1)
);
```

`setJsonRemove()` deletes a key or array element:

```php
$db->execute(
    $db->createQuery()
        ->update('articles')
        ->setJsonRemove('params->legacy')
        ->where('id', 1)
);
```

### As an Expression: `jsonSet()` / `jsonRemove()`

`jsonSet()` and `jsonRemove()` return a raw expression usable as a `set()` value, so the source and target column can
differ, and a `jsonSet()` can be nested as the value of another:

```php
$query = $db->createQuery()->update('articles');

$query->set('params', $query->jsonSet('params->views', 100))
    ->set('backup', $query->jsonSet('params->copy', $query->jsonSet('other->n', 5)));
```

`setJson()` / `setJsonRemove()` are shortcuts for `set($column, jsonSet(...))` / `set($column, jsonRemove(...))`.

### Platform Notes and Limitations

- Calling `setJson()` / `setJsonRemove()` several times for the **same column** in one `UPDATE` produces duplicate
  assignments (`SET params = ..., params = ...`). MySQL tolerates this, but PostgreSQL and SQL Server reject it. Update
  different columns, or run separate statements.
- **PostgreSQL**: the column is cast to `jsonb`. `jsonb_set` only creates the last missing key; missing intermediate
  keys are not created.
- **SQL Server**: `whereJsonExists()` uses `JSON_PATH_EXISTS` and requires SQL Server 2022 or later. `setJson()` with
  `null` removes the key.
- **Oracle**: `setJson()` / `setJsonRemove()` use `JSON_TRANSFORM` and require Oracle 19c or later.
- Double-quoted path segments containing an escaped quote (`\"`) are not supported.
