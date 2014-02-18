# Windwalker DataMapper Package

## Installation via Composer

Add `"asika/windwalker-middleware": "dev-master"` to the require block in your composer.json, make sure you have "minimum-stability": "dev" and then run composer install.

``` json
{
    "require": {
        "windwalker/datamapper": "dev-master"
    }
}
```

## Getting Started

### Prepare Database object

``` php
use Windwalker\DataMapper\Database\DatabaseFactory;

// Make the database driver.
$db = DatabaseFactory::getDbo(
	array(
		'driver'   => 'mysql',
		'host'     => 'localhost',
		'user'     => 'root',
		'password' => 'xxxx',
		'database' => 'mydb',
		'prefix'   => 'prefix_'
	)
);
```

You can get Database later by Factory:

``` php
$db = DatabaseFactory::getDbo();
```

See Joomla Database: https://github.com/joomla-framework/database

## Create a DataMapper

``` php
use Windwalker\DataMapper\DataMapper;

$fooMapper = new DataMapper('#__foo');

$fooSet = $fooMapper->find(array('id' => 1));
```

## Find Records

Find method will fetch rows from table, and return `DataSet` class.

### find()

Get `id = 1` record

``` php
$fooSet = $fooMapper->find(array('id' => 1));
```

Fetch `published = 1`, and sort by `date`

``` php
$fooSet = $fooMapper->find(array('published' => 1), 'date');
```

Fetch `published = 1, language = en-US`, sort by `date` DESC and start with `30`, limit `10`.

``` php
$fooSet = $fooMapper->find(array('published' => 1, 'language' => 'en-US'), 'date DESC', 30, 10);
```

### findOne

Just return one row.

``` php
$foo = $dooMapper->findOne(array('published' => 1), 'date');
```

### findAll

Equal to `find(array(), $order, $start, $limit)`.

## Create Records

Using DataSet to wrap every data, then send this object to create() method, these data will insert to table.

### create()

``` php
use Windwalker\Data\Data;
use Windwalker\Data\DataSet;

$data1 = new Data;
$data1->title = 'Foo';
$data1->auhor = 'Magneto';

$data2 = new Data(
    array(
        'title' => 'Bar',
        'author' => 'Wolverine'
    )
);

$dataset = new DataSet(array($data1, $data2));

$return = $fooMapper->create($dataset);
```

The return value will be whole dataset and add inserted ids.

```
Windwalker\Data\DataSet Object
(
    [storage:ArrayObject:private] => Array
        (
            [0] => Windwalker\Data\Data Object
                (
                    [title] => Foo
                    [auhor] => Magneto
                    [id] => 39
                )

            [1] => Windwalker\Data\Data Object
                (
                    [title] => Bar
                    [auhor] => Wolverine
                    [id] => 40
                )
        )
)
```

### createOne()

Only insert one row, do not need DataSet.

``` php
$data = new Data;
$data->title = 'Foo';
$data->auhor = 'Magneto';

$fooMapper->createOne($data);
```


## Update Records

Update methods help us update rows in table.

### update()

``` php
use Windwalker\Data\Data;
use Windwalker\Data\DataSet;

$data1 = new Data;
$data1->id = 1;
$data1->title = 'Foo';

$data2 = new Data(
    array(
        'id' => 2,
        'title' => 'Bar'
    )
);

$dataset = new DataSet(array($data1, $data2));

$fooMapper->update($dataset);
```

### updateOne

Just update one row.

``` php
$data = new Data;
$data->id = 1;
$data->title = 'Foo';

$fooMapper->updateOne($data);
```

### updateAll

UpdateAll is different from update method, we just send one data object, but using conditions as where
to update every row match these conditions. We don't need primary key for updateAll().

``` php
$data = new Data;
$data->published = 0;

$fooMapper->updateAll($data, array('author' => 'Mystique'));
```

## Delete

Delete rows by conditions.

### delete()

``` php
$boolean = $fooMapper->delete(array('author' => 'Jean Grey'));
```

## Using Data and DataSet

Please see: https://github.com/windwalker-framework/data



