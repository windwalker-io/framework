> Draft: to be merged into the official docs.

### Customizing Written Columns

`DatabaseHandler` writes `id`, `data` and `time` by default. If your session table has extra columns (for example
`user_id` or `ip`), pass a `Closure` as the `writeHandler` constructor argument (also available as the public `writeHandler` property) that adjusts the values before they are saved.

```php
use Windwalker\Session\Handler\DatabaseHandler;

$handler = new DatabaseHandler(
    $db,
    writeHandler: static function (array $values, string $id, string $data) use ($request): array {
        $values['ip'] = $request->getServerParams()['REMOTE_ADDR'] ?? null;

        return $values;
    }
);
```

The Closure receives:

- `$values`: the column map to be written, keyed by the real column names (respecting the `columns` option).
- `$id`: the session ID.
- `$data`: the serialized session data.

It must return the complete column map. You can add columns or override defaults such as `time`.

#### Behaviors

- Every returned column except the ID column is also updated when the session already exists, so custom columns
  stay in sync on each write.
- The ID column is always restored to the session ID, so it cannot be changed by the Closure.
- `writeHandler` defaults to `null`, which keeps the original behavior (`id`, `data`, `time` only).
- The Closure is not called by `updateTimestamp()` (the lazy-write path when session data is unchanged), so custom
  columns are not refreshed in that case.
- Returned columns must exist in the session table.
