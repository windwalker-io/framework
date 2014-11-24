# Table Command

Get Table Command.

``` php
$table = $db->getTable('#__articles');
```

## create()

Create a new table.

> *NOTE:* Table has not support foreign key now.

``` php
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;
use Windwalker\Database\Schema\DataType;

$table = $db->getTable('#__articles');

$table->addColumn('id', DataType::INTEGER, Column::UNSIGNED, Column::NOT_NULL, '', 'PK', array('primary' => true))
    ->addColumn('name', DataType::VARCHAR, Column::SIGNED, Column::NOT_NULL, '', 'Name', array('length' => 255))
    ->addColumn('alias', DataType::VARCHAR, Column::SIGNED, Column::NOT_NULL, '', 'Alias')
    ->addIndex(Key::TYPE_INDEX, 'idx_name', 'name', 'Test')
    ->addIndex(Key::TYPE_UNIQUE, 'idx_alias', 'alias', 'Alias Index')
    ->create(true); // True to add IF NOT EXISTS
```

Using Column type objects:

``` php
use Windwalker\Database\Schema\Column;

$table->addColumn(new Column\Primary('id'))
    ->addColumn(new Column\Varchar('name'))
    ->addColumn(new Column\Char('type'))
    ->addColumn(new Column\Timestamp('created'))
    ->addColumn(new Column\Bit('state'))
    ->addColumn(new Column\Integer('uid'))
    ->addColumn(new Column\Tinyint('status'))
    ->create();
```

These objects will set default length and attributes for every column, and map the type to different database drivers.

## update()

Update table schema. Only use on add columns and indexes.

``` php
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;

$table = $db->getTable('#__articles');

$table->addColumn('state', DataType::TINYINT, Column::SIGNED, Column::NOT_NULL, 0, 'State', array('position' => 'AFTER ordering', 'length' => 1))
    ->addIndex(Key::TYPE_INDEX, 'idx_ordering', array('ordering', 'id'))
    ->update();
```

## save()

If table exists, using update, otherwise use insert.

``` php
use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\Key;

$table = $db->getTable('#__articles');

$table->addColumn('state', DataType::TINYINT, Column::SIGNED, Column::NOT_NULL, 0, 'State', array('position' => 'AFTER ordering', 'length' => 1))
    ->addIndex(Key::TYPE_INDEX, 'idx_ordering', array('ordering', 'id'))
    ->save();
```

## dropColumn()

``` php
$table = $db->getTable('#__articles');

$table->dropColumn('state');
```

## dropIndex()

``` php
use Windwalker\Database\Schema\Key;

$table = $db->getTable('#__articles');

$table->dropIndex(Key::TYPE_INDEX, 'idx_state');
```

## rename()

``` php
$table = $db->getTable('#__foo');

$newTable = $table->rename('#__bar');
```

## truncate()

``` php
$table = $db->getTable('#__foo');

$table->truncate();
```
