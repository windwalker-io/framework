> Draft: to be merged into the official docs, then delete this file.

## Generated Columns and Functional Indexes

A generated column is a column whose value is computed by the database from an expression, usually based on other
columns in the same row. A functional index is an index built on an expression (e.g. `LOWER(email)`) instead of a
plain column. Combined, they let you query and index derived values without maintaining them in application code.

### Generated columns

Use `virtual()` or `stored()` on any column definition in the schema builder:

```php
use Windwalker\Database\Schema\Schema;

$db->getTableManager('products')->create(
    static function (Schema $schema) {
        $schema->primary('id');
        $schema->integer('price');

        // Computed on read, takes no storage
        $schema->integer('price_with_tax')->virtual('price * 1.05');

        // Computed on write, stored on disk
        $schema->integer('double_price')->stored('price * 2');
    }
);
```

`generated(string|RawWrapper $expression, bool $stored = false)` is the underlying method; `virtual()` and
`stored()` are shortcuts. The expression is inserted into the DDL as-is, so only pass trusted code, never user input.

Generated columns can also be added to an existing table with `TableManager::addColumn()`.

Behaviors:

- A generated column never gets a `DEFAULT` clause, even if a default value is set.
- When columns are loaded from MySQL, the expression and the stored flag are read back, so operations such as
  `renameColumn()` keep the generated definition.

### Functional indexes

Pass expressions wrapped by `raw()` to `Schema::addFunctionalIndex()`. Plain strings are treated as column names, so
columns and expressions can be mixed. A name is required:

```php
use function Windwalker\raw;

$db->getTableManager('users')->create(
    static function (Schema $schema) {
        $schema->primary('id');
        $schema->varchar('email');
        $schema->varchar('country');

        $schema->addFunctionalIndex([raw('LOWER(email)')], 'idx_users_email_lower');
        $schema->addFunctionalIndex(['country', raw('LOWER(email)')], 'idx_users_country_email');
    }
);
```

To add one to an existing table, build an `Index` and use `TableManager::addIndex()`:

```php
use Windwalker\Database\Schema\Ddl\Index;

$index = (new Index('idx_users_email_lower'))->expression('LOWER(email)');

$db->getTableManager('users')->addIndex($index);
```

Each expression is wrapped in parentheses automatically (required by MySQL 8.0.13+ and PostgreSQL).

### Platform support

| Platform   | Generated column                                          | Functional index          |
|------------|-----------------------------------------------------------|---------------------------|
| MySQL      | `VIRTUAL` / `STORED`                                      | Supported (8.0.13+)       |
| PostgreSQL | `STORED`; `VIRTUAL` requires PostgreSQL 18+               | Supported                 |
| SQLite     | `VIRTUAL` / `STORED` (see below)                          | Supported                 |
| SQL Server | `AS (expr)` for virtual, `AS (expr) PERSISTED` for stored | Not supported             |

The framework does not silently convert unsupported cases. Instead:

- Using `virtualAs()` on PostgreSQL older than 18 throws a `LogicException`; use `storedAs()` instead.
- Adding a functional index on SQL Server throws a `LogicException`. Create a computed column and index it
  instead:

  ```php
  $schema->varchar('email_lower')->storedAs('LOWER(email)');
  $schema->addIndex('email_lower');
  ```

SQLite cannot add a `STORED` generated column to an existing table with `ALTER TABLE ADD COLUMN`; define it when
creating the table.

When an existing index is read back, expression key parts have no real column. They are listed under a column name
that is the expression itself (PostgreSQL) or a placeholder like `(expression 1)` (MySQL, SQLite).
