# Table Command

Get Table Command.

``` php
$table = $db->getTable('#__articles');
```

## create()

Create a new table.

> *NOTE:* Table has not support foreign key now.

``` php
use Windwalker\Database\Schema\Schema;

$table = $db->getTable('#__articles');

$table->create(function (Schema $schema)
{
    $schema->primary('id')->signed(false)->allowNull(false)->comment('Primary Key');
    $schema->integer('category_id')->signed(false)->allowNull(false)->comment('Cat Key');
    $schema->varchar('title')->allowNull(false)->comment('Title');
    $schema->varchar('slug')->length(123)->allowNull(false)->comment('Slug');
    $schema->text('content')->allowNull(false)->comment('Content');

	$schema->addIndex('category_id');
	$schema->addIndex(array('category_id', 'title'));
	$schema->addUniqueKey('slug');
}, true); // True to add IF NOT EXISTS
```

Available types in `Schema` object:

- primary (An `integer` column with primary key and auto-increment)
- integer
- bigint
- tinyint
- bit
- float
- double
- decimal
- char
- varchar
- datetime
- timestamp
- text
- longtext

### Using Column type objects:

``` php
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Schema\Column\Varchar;

    // ...

    $schema->add('title', new Varchar)->allowNull(false)->comment('Title');

    // ...
```

You can create your own Column type if you want, just extends `Windwalker\Database\Schema\Column` object.

## Type Mapping

Windwalker supports MySQL and Postgresql now, and we'll add more database driver in the future. Every SQL platform has their
 own data types, so Windwalker has a `DataType` class to help us choose correct these types to current SQL.

For example, Postgresql has no `datatime` type, but MySQL does, so if we create a table with `datetime`, Windwalker will
 convert this type to `timestamp` if we use postgresql driver.

``` php
    // ...

    $schema->datetime('created'); // Will be `timestamp` if use pgsql

    // ...
```

We can get type detail by `DataType` class.

``` php
use Windwalker\Database\Driver\Mysql\MysqlType;
use Windwalker\Database\Driver\Postgresql\PostgresqlType;
use Windwalker\Database\Schema\DataType;

// Mysql has `set` type, but pgsql not.
// We'll get `text` instead
$type = PostgresqlType::getType('set'); // text

// Most SQL use `integer` type, but MySQL is `int`
$type = MysqlType::getPhpType('integer'); // int

// Get default length, varchar default length in MySQL is 255
// Every type has a constant so we can get it easily if your IDE supports auto-complete
MysqlType::getLength(MysqlType::VARCHAR); // 255

// Get SQL type map to php type
gettype($entity->created) == MysqlType::getPhpType(DataType::DATETIME); // Will be `string`
gettype($entity->state) == MysqlType::getPhpType(DataType::TINYINT); // Will be `int`

// Get valid default value
MysqlType::getDefaultValue(DataType::DATETIME); // `0000-00-00 00:00:00`
MysqlType::getDefaultValue(DataType::TINYINT); // `0`
```

### Add Indexes

We can add a single name or a set of columns name, the index name will auto created.

``` php
$schema->addIndex('foo'); // idx_tablename_foo
$schema->addIndex(array('foo', 'bar')); // idx_tablename_foo_bar
```

You can also set a custom name

``` php
$schema->addIndex(array('foo', 'bar'), 'idx_custom_name');
```

## update()

``` php
// Will add category_id column and a index
$table->create(function (Schema $schema)
{
    $schema->integer('category_id')->signed(false)->allowNull(false)->comment('Cat Key');

	$schema->addIndex('category_id');
});
```

## save()

If table exists, using update, otherwise use insert.

``` php
// Will create table if not exists, and add columns if not in table.
$table->save(function (Schema $schema)
{
    $schema->primary('id')->signed(false)->allowNull(false)->comment('Primary Key');
    $schema->integer('category_id')->signed(false)->allowNull(false)->comment('Cat Key');
    $schema->varchar('title')->allowNull(false)->comment('Title');
    $schema->varchar('slug')->length(123)->allowNull(false)->comment('Slug');
    $schema->text('content')->allowNull(false)->comment('Content');

	$schema->addIndex('category_id');
	$schema->addIndex(array('category_id', 'title'));
	$schema->addUniqueKey('slug');
}, true); // True to add IF NOT EXISTS
```

## dropColumn()

Instantly drop column.

``` php
$table = $db->getTable('#__articles');

$table->dropColumn('state');
```

## dropIndex()

Instantly drop index.

``` php
use Windwalker\Database\Schema\Key;

$table = $db->getTable('#__articles');

$table->dropIndex(Key::TYPE_INDEX, 'idx_state');
```

## changeColumn() & modifyColumn()

``` php
// Modify `foo` column to new type or length
$table->modifyColumn(
	(new Varchar('foo'))->length(123)->comment('Foo')
);

// Modify column `foo` to varchar and rename it to `new_name`
$table->changeColumn('foo', new Varchar('new_name'));
```

## Rename Table

``` php
$table = $db->getTable('#__foo');

$newTable = $table->rename('#__bar');
```

## Truncate Table

``` php
$table = $db->getTable('#__foo');

$table->truncate();
```

## Get Column

``` php
// Get full columns data
$table->getColumnDetails();

// Get single column data
$table->getColumnDetail('name');

// Get an array with columns name
$table->getColumns();
```
