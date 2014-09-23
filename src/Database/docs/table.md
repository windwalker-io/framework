# Table Command

Get Table Command.

``` php
$table = $db->getTable('#__articles');
```

## create()

Create a new table.

> *NOTE:* Table do not support foreign key now.

``` php
use Windwalker\Database\Command\Table\Column;
use Windwalker\Database\Command\Table\Key;

$table = $db->getTable('#__articles');

$table->addColumn('id', 'int(11)', Column::UNSIGNED, Column::NOT_NULL, '', 'PK', array('primary' => true))
    ->addColumn('name', 'varchar(255)', Column::SIGNED, Column::NOT_NULL, '', 'Name')
    ->addColumn('alias', 'varchar(255)', Column::SIGNED, Column::NOT_NULL, '', 'Alias')
    ->addIndex(Key::TYPE_INDEX, 'idx_name', 'name', 'Test')
    ->addIndex(Key::TYPE_UNIQUE, 'idx_alias', 'alias', 'Alias Index')
    ->create(true); // True to add IF NOT EXISTS
```

## update()

Update table schema. Only use on add columns and indexes.

``` php
use Windwalker\Database\Command\Table\Column;
use Windwalker\Database\Command\Table\Key;

$table = $db->getTable('#__articles');

$table->addColumn('state', 'int(1)', Column::SIGNED, Column::NOT_NULL, 0, 'State', array('position' => 'AFTER ordering'))
    ->addIndex('key', 'idx_ordering', array('ordering', 'id'))
    ->update();
```

## save()

If table exists, using update, otherwise use insert.

``` php
use Windwalker\Database\Command\Table\Column;
use Windwalker\Database\Command\Table\Key;

$table = $db->getTable('#__articles');

$table->addColumn('state', 'int(1)', Column::SIGNED, Column::NOT_NULL, 0, 'State', array('position' => 'AFTER ordering'))
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
use Windwalker\Database\Command\Table\Key;

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
