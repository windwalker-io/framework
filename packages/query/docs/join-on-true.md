> Draft: to be merged into the official docs, then delete this file.

## ON TRUE for Joins

Pass `true` as the join condition to render `ON TRUE`. Use it when every row should match, for example with a lateral
sub-query that already limits its own rows:

```php
$query = $db->createQuery()
    ->select('a.id')
    ->from('a')
    ->join('left', 'b', null, true);
```

```sql
SELECT "a"."id" FROM "a" LEFT JOIN "b" ON TRUE
```

`true` is only accepted as the single condition. Other values still use the normal `column, operator, value` form.

### Lateral Joins Default to ON TRUE

`joinLateral()` and the `leftJoinLateral()`, `rightJoinLateral()`, `innerJoinLateral()` shortcuts do not need a
condition. When none is given, they use `ON TRUE`:

```php
$query = $db->createQuery()
    ->select('a.id')
    ->from('a')
    ->leftJoinLateral(
        $db->createQuery()
            ->select('b.id')
            ->from('b')
            ->where('b.a_id', 1),
        'x'
    );
```

```sql
SELECT "a"."id" FROM "a" LEFT JOIN LATERAL (SELECT "b"."id" FROM "b" WHERE "b"."a_id" = ?) AS "x" ON TRUE
```

Pass a condition as usual to override the default:

```php
$query->joinLateral('inner', $sub, 'x', 'a.id', '=', 'x.id');
```
