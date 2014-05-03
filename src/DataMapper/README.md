# Windwalker DataMapper Package

## Installation via Composer

Add `"ventoviro/windwalker-middleware": "1.0.*"` to the require block in your composer.json.

``` json
{
    "require": {
        "ventoviro/windwalker-datamapper": "1.0.*"
    }
}
```

## Getting Started

### Prepare Database object

``` php
use Windwalker\Database\DatabaseFactory;

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

Get id = 1 record

``` php
$fooSet = $fooMapper->find(array('id' => 1));
```

Fetch published = 1, and sort by `date`

``` php
$fooSet = $fooMapper->find(array('published' => 1), 'date');
```

Fetch published = 1, language = en-US, sort by `date` DESC and start with `30`, limit `10`.

``` php
$fooSet = $fooMapper->find(array('published' => 1, 'language' => 'en-US'), 'date DESC', 30, 10);
```

Using array, will be `IN` condition:

``` php
$fooSet = $fooMapper->find(array('id' => array(1,2,3))); // WHERE id IN (1,2,3)
```

### findOne()

Just return one row.

``` php
$foo = $dooMapper->findOne(array('published' => 1), 'date');
```

### findAll()

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

### updateOne()

Just update one row.

``` php
$data = new Data;
$data->id = 1;
$data->title = 'Foo';

$fooMapper->updateOne($data);
```

### updateAll()

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

## Join Tables

Using `RelationDataMapper` to join tables.

``` php
$fooMapper = new RelationDataMapper('foo', '#__foo');

$fooMapper->addTable('author', '#__users', 'foo.user_id = author.id', 'LEFT')
    ->addTable('category', '#__categories', array('category.lft >= foo.lft', 'category.rgt <= foo.rgt'), 'INNER');

// Don't forget add alias on where conditions.
$dataset = $fooMapper->find(array('foo.id' => 5));
```

The Join query will be:

``` sql
FROM #__foo AS foo
    LEFT JOIN #__users AS author ON foo.user_id = author.id
    INNER JOIN #__categories AS category ON category.lft >= foo.lft AND category.rgt <= foo.rgt
```

### Using OR Condition

``` php
$fooMapper->addTable(
    'category',
    '#__categories',
    'category.lft >= foo.lft OR category.rgt <= foo.rgt',
    'LEFT'
);
```

## Compare objects

Using Compare objects help us set some where conditions which hard to use array to defind.

``` php
$fooSet = $fooMapper->find(
    array(
        new GteCompare('id', 5),
        new NeqCompare('name', 'bar')
        new LtCompare('published', 1),
        new NinCompare('catid', array(1,2,3,4,5))
    )
);
```

This will generate where conditions like below:

``` sql
WHERE `id` >= '5'
    AND `name` != 'bar'
    AND `published` < '1'
    AND `catid` NOT IN (1,2,3,4,5)
```

### Abailable compares:

- EqCompare  : Equal `=`
- NeqCompare : Not Equal `!=`
- GtCompare  : Greater than `>`
- GteCompare : Greate than or Equal `>=`
- LtCompare  : Less than `<`
- LteCompare : Less than or Equal `<=`
- InCompare  : In `IN`
- NinCompare : Not In `NOT IN`

### Custom Compare

``` php
echo (string) new Compare('title', '%flower%', 'LIKE');
```

Will be

``` sql
`title` LIKE `%flower%`
```

See: https://github.com/ventoviro/windwalker-compare


## Using Data and DataSet

See: https://github.com/windwalker-framework/data



