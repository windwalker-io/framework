> Draft: to be merged into the official docs.

## Check Constraints

A check constraint makes the database reject any row that does not satisfy a boolean expression, for example
`price >= 0`. `Schema` can now declare check constraints when creating or altering a table.

### Usage

Declare a check with `Schema::addCheck()`. It takes the SQL expression and an optional constraint name.

```php
$table->create(function (Schema $schema) {
    $schema->primary('id');
    $schema->decimal('price')->length('20,4');
    $schema->integer('stock');

    $schema->addCheck('price >= 0', 'ck_product_price');
    $schema->addCheck('stock >= 0');
});
```

When the name is omitted, it is generated as `ck_{table}_{hash of expression}`.

Add a check to an existing table with `TableManager::addCheck()`, which takes the same arguments. Drop it with
`dropConstraint()`. Adding a check whose name already exists is a no-op.

```php
$table->addCheck('price >= 0', 'ck_product_price');

$table->dropConstraint('ck_product_price');
```

Existing checks are listed by `TableManager::getConstraints()`. Use `isCheck()` and `$checkClause` to read them.

```php
$constraint = $table->getConstraint('ck_product_price');

$constraint->isCheck();      // true
$constraint->checkClause;    // the expression as stored by the database
```

### Behaviors and limitations

- The expression is written to SQL as-is; do not pass untrusted input.
- `checkClause` is the expression as the database stores it, which may differ in formatting from what you wrote
  (e.g. added parentheses or casts).
- Schema diff only compares the name and type of a check, not its expression, because databases rewrite
  expressions. To change an expression, drop the constraint and add it again.
- MySQL requires 8.0.16+ (or MariaDB 10.2+) for checks to be enforced.
- SQLite supports checks only when creating a table. Adding a check to an existing table throws a `LogicException`,
  and existing checks are not returned by `getConstraints()`.
