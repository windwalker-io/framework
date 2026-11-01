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
two writes in the same second cannot be told apart. Use `DATETIME(6)`, or prefer an integer version.

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
