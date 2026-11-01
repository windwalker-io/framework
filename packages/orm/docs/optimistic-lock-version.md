> Draft: to be merged into the official docs.

## Optimistic Lock Column Types

`#[OptimisticLock]` marks the property used to detect concurrent updates. On every update, the mapper adds
`WHERE {column} = {value loaded}` and writes the next value. If no row is affected, an `OptimisticLockException` is
thrown.

The next value depends on the property type:

| Type                | Next value                |
|---------------------|---------------------------|
| `int`               | previous value + 1        |
| `float`             | previous value + 1.0      |
| `string`            | `uid()`                   |
| `\DateTimeInterface`| `new \DateTimeImmutable('now')` |

### Recommended: integer version

```php
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\OptimisticLock;

#[Column('version')]
#[OptimisticLock]
public int $version = 0;
```

An integer is not affected by column precision and distinguishes updates made within the same second. A value of `0`
is a valid version and is included in the `WHERE` condition. Only `null` skips the check.

### Date/time columns

The new value carries microseconds. If the column is a plain `DATETIME` (seconds precision), the database truncates it,
so updating the same entity again right away fails because the in-memory value no longer matches the stored one. Also,
two writes in the same second cannot be told apart.

A time-based lock is only reliable with a microsecond-precision column. Choose the type yourself for your database:

| Database   | Recommended column type |
|------------|-------------------------|
| MySQL / MariaDB | `DATETIME(6)` |
| PostgreSQL | `timestamp(6)` |
| SQL Server | `datetime2(6)` |
| SQLite     | no real date type; use an integer version |

`Schema` accepts any type name (unknown method names become the column type), so you can declare the type directly:

```php
$schema->datetime('updated')->length(6);    // MySQL: datetime(6)
$schema->timestamp('updated')->length(6);   // PostgreSQL: timestamp(6)
$schema->datetime2('updated')->length(6);   // SQL Server: datetime2(6)
```

The same works on MySQL/MariaDB: `$schema->datetime('updated')->length(6);` produces `datetime(6)`.

Two writes within the same microsecond are still theoretically indistinguishable. If you need a portable or strictly
safe lock, use an integer version.

### Custom next value

Pass a callable to control how the next value is generated:

```php
#[OptimisticLock(nextHandler: fn(mixed $value) => hash('sha256', uniqid()))]
public string $hash = '';
```

### Disabling the check

```php
use Windwalker\ORM\ORMOptions;

$orm->updateOne($entity, options: new ORMOptions(optimisticLock: false));
```
