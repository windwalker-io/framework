> Draft: to be merged into the official docs, then delete this file.

## WITH (Common Table Expressions)

`with()` adds a common table expression (CTE) in front of the main statement, producing
`WITH name AS (...) SELECT ...`. Use it to name an intermediate result set, reuse it in `FROM` / `JOIN`, or build
recursive queries such as tree traversal.

### Basic Usage

The second argument can be a `Query` object, a `Closure` that receives a sub-query, or a raw SQL string.

```php
$query = $db->createQuery()
    ->with(
        'recent',
        fn(Query $q) => $q->select('*')->from('articles')->where('created', '>', $since)
    )
    ->select('*')
    ->from('recent')
    ->where('state', 1);
```

```sql
WITH "recent" AS (SELECT * FROM "articles" WHERE "created" > ?)
SELECT * FROM "recent" WHERE "state" = ?
```

Call `with()` multiple times to define several CTEs. They are joined by commas in the order they were added.

Bound values of the CTE queries are merged into the main query automatically, so you do not need to bind them
again.

### Column List

Pass the column names as the third argument:

```php
$query->with('cats', 'SELECT id, title FROM categories', ['id', 'title']);
```

```sql
WITH "cats" ("id", "title") AS (SELECT id, title FROM categories)
```

### Recursive CTE

Use `withRecursive()` to emit `WITH RECURSIVE`. Build the CTE body as a full `UNION` query: the first
`unionAll()` / `union()` call is the anchor and the next is the recursive part, which refers to the CTE name itself.

```php
$tree = $db->createQuery()
    ->unionAll(
        $db->createQuery()->select('id', 'parent_id')->from('nodes')->where('id', 7)
    )
    ->unionAll(
        $db->createQuery()
            ->select('n.id', 'n.parent_id')
            ->from('nodes', 'n')
            ->join('INNER', 'tree', 't', 'n.parent_id', '=', 't.id')
    );

$query = $db->createQuery()
    ->withRecursive('tree', $tree, ['id', 'parent_id'])
    ->select('*')
    ->from('tree');
```

The first call of `union()` / `unionAll()` decides the union type used for the whole CTE body.

If any CTE of a query is recursive, the whole `WITH` clause becomes `WITH RECURSIVE`.
`with()` also accepts a `$recursive` argument, which is what `withRecursive()` uses.

### MATERIALIZED / NOT MATERIALIZED

Pass `$materialized` to give the database an optimizer hint:

```php
$query->with('a', $subQuery, materialized: true);   // AS MATERIALIZED (...)
$query->with('b', $subQuery, materialized: false);  // AS NOT MATERIALIZED (...)
$query->withRecursive('tree', $tree, ['id'], materialized: true);
```

`null` (default) emits no hint.

### Behaviors and Limitations

| Feature | MySQL | PostgreSQL | SQLite | SQL Server | Oracle |
|---|---|---|---|---|---|
| `WITH` | ✔ (8.0+) | ✔ | ✔ | ✔ | ✔ |
| `RECURSIVE` keyword | ✔ | ✔ | ✔ | omitted | omitted |
| `MATERIALIZED` hint | ignored | ✔ (12+) | ✔ (3.35+) | ignored | ignored |

- The `WITH` clause is also added to `insert`, `update` and `delete` statements; whether the statement is valid
  depends on the database.
- SQL Server and Oracle do not use the `RECURSIVE` keyword, so it is left out automatically.
- Dialects that do not support the `MATERIALIZED` hint silently ignore the `$materialized` argument.
- `clear('with')` removes all CTEs and the recursive flag.
