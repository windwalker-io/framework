# Database Testing Environment

How to set up and write tests that need a real database (`packages/database`, `packages/orm`, session, etc.).
MySQL is the default and best-covered platform; PostgreSQL / SQL Server / SQLite use the same mechanism.

## 1. Provide a database

Option A – local MySQL (no password root is assumed):

```bash
mysql -uroot -e "SELECT VERSION();"   # confirm it is reachable
```

Option B – Docker:

```bash
docker run -d --name ww-mysql -p 3306:3306 -e MYSQL_ALLOW_EMPTY_PASSWORD=yes mysql:8
```

The test base class creates and drops the database itself, so no manual schema import is needed.

## 2. DSN configuration

Tests read DSNs from constants in the root `phpunit.xml` (copy `phpunit.xml.dist` if missing):

```xml
<php>
    <const name="WINDWALKER_TEST_DB_DSN_MYSQL"
           value="host=127.0.0.1;dbname=windwalker_test;user=root;password=;prefix=ww_"/>
    <!-- also: _POSTGRESQL, _SQLSERVER, _SQLITE, _MARIADB -->
</php>
```

- A missing/empty DSN makes the matching tests **skip**, not fail. Running with `--no-configuration` therefore
  skips everything — always keep the project config.
- Table prefix `ww_` is applied to fixture tables; test tables you create yourself can be unprefixed.

## 3. Running

```bash
php vendor/bin/phpunit packages/database/test
php vendor/bin/phpunit packages/orm/test
php vendor/bin/phpunit packages/database/test/Manager/WriterManagerTest.php
```

**Never run two DB suites concurrently** (including parallel agents/terminals): test classes drop and recreate the
same database, producing bogus `Unknown database` / missing-table errors. Run them sequentially.

Executed SQL is logged to `packages/database/tmp/test-sql.sql` — check it when debugging.

Scoped coverage (keep the project config; the filter leaks other packages, so filter the report afterwards):

```bash
XDEBUG_MODE=coverage php vendor/bin/phpunit \
  --coverage-filter packages/database/src --coverage-filter packages/orm/src \
  --coverage-clover /tmp/db.xml packages/database/test packages/orm/test
```

## 4. Writing tests

- Extend `Windwalker\Database\Test\AbstractDatabaseTestCase` (MySQL by default). Use `static::$db`
  (`DatabaseAdapter`) and `static::$baseConn` (raw PDO). Define fixtures in `setupDatabase()`.
- ORM tests extend `AbstractORMTestCase`.
- Reset state per test with `#[Before]` (e.g. `TRUNCATE`) or wrap in a transaction and roll back.
- For order-dependent suites (e.g. `*TableManagerTest`, one state machine from create to drop), do not mutate
  shared state: use a temp table dropped in `finally`, or `clone` the manager.
- `DatabaseAdapter::getTableManager()` returns a cached shared instance — never `setName()` on it.
  `TableManager` caches columns/indexes/constraints; call `reset()` after DDL before re-checking.
- Helpers: `logQueries(fn)`, `assertSqlFormatEquals()`, `assertArraySimilar()`.
- Mark platform-specific tests with the right base class/driver so they skip when the DSN is absent.
