# Database Testing Environment

How to set up and write tests that need a real database (`packages/database`, `packages/orm`, session, etc.).
MySQL is the default and best-covered platform; PostgreSQL / SQL Server / SQLite use the same mechanism.

## 1. Provide a database

Option A – local MySQL (no password root is assumed):

```bash
mysql -uroot -e "SELECT VERSION();"   # confirm it is reachable
```

Option B – Docker: (Mostly we don't use this way.)

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
    <!-- also: _POSTGRESQL, _SQLSERVER, _ORACLE, _SQLITE, _MARIADB -->
</php>
```

- A missing/empty DSN makes the matching tests **skip**, not fail. Running with `--no-configuration` therefore
  skips everything — always keep the project config.
- Table prefix `ww_` is applied to fixture tables; test tables you create yourself can be unprefixed.

### Oracle

Oracle uses the `oracle` docker service (`gvenzl/oracle-free`, app user `windwalker`) and requires `pdo_oci`.
`dbname` is the **service name** (`FREEPDB1`), not a database to create. The app user has no `CREATE USER`
privilege, so the create/drop database/schema tests are skipped. Unquoted identifiers fold to UPPERCASE; the
Query layer quotes and upper-cases everything.

## 3. Running

```bash
php vendor/bin/phpunit packages/database/test
php vendor/bin/phpunit packages/orm/test
php vendor/bin/phpunit packages/database/test/Manager/WriterManagerTest.php
```

**Never run two DB suites concurrently with the same database** (including parallel agents/terminals; use a distinct random `WINDWALKER_TEST_DB_SUFFIX` per run to isolate them, see below): test classes drop and recreate the
same database, producing bogus `Unknown database` / missing-table errors. Run them sequentially.

### Parallel agents: database suffix

Set `WINDWALKER_TEST_DB_SUFFIX` to isolate runs. It is appended to the DSN `dbname` (`windwalker_test` →
`windwalker_test_k3x9q`; for SQLite, inserted before the file extension). Each distinct suffix gets its own
database, so suites with different suffixes **can** run concurrently:

**Always use a random 4–6 character alphanumeric suffix** (e.g. `k3x9q`), generated fresh for every run. Fixed
names like `agent1` collide when several agents/worktrees pick the same one.

```bash
WINDWALKER_TEST_DB_SUFFIX=$(LC_ALL=C tr -dc 'a-z0-9' </dev/urandom | head -c 5) php vendor/bin/phpunit packages/database/test
WINDWALKER_TEST_DB_SUFFIX=k3x9q php vendor/bin/phpunit packages/orm/test   # or a literal random value
```

Non-alphanumeric characters become `_`. With a suffix set, the suffixed database is dropped after each test class and
again at process shutdown (so errors/fatals don't leave it behind); the unsuffixed database is never dropped. Never share one suffix between concurrent runs. Executed SQL is still
logged to one shared file, so don't rely on it when running in parallel.

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

## 5. Testing a small feature on a non-default database

`AbstractDatabaseTestCase` targets MySQL. To cover one feature on PostgreSQL / SQL Server / SQLite (e.g. `upsert()`):

1. Put the platform-agnostic tests in an abstract test case (see `test/Manager/AbstractUpsertTestCase.php`) that
   declares an abstract static fixture method (DDL differs per platform) and calls it from `setupDatabase()`.
2. Add one thin subclass per platform that overrides the static properties and the DDL only:

   ```php
   class PostgreSQLWriterManagerTest extends AbstractUpsertTestCase
   {
       protected static DbPlatform $platform = DbPlatform::PostgreSQL;
       protected static string $driver = 'pdo_pgsql';   // pdo_sqlsrv, pdo_sqlite, ...
       protected static string $schema = 'public';      // PostgreSQL only
   }
   ```

   Forgetting `$driver` silently falls back to `pdo_mysql` (symptom: MySQL "Access denied" in a SQLite test).
3. Create fixtures through `static::$baseConn` with platform-specific SQL (`SERIAL`, `IDENTITY(1,1)`,
   `AUTOINCREMENT`); use unprefixed table names such as `ww_upsert_items`.
4. A missing DSN skips the class; confirm the server is reachable (`nc -z 127.0.0.1 5432` / `1433`) so tests don't
   skip silently. Run each platform's class on its own, sequentially.
