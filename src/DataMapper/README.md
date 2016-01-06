# Windwalker DataMapper

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/datamapper": "~2.0",
        "windwalker/database": "~2.0"
    }
}
```

## Getting Started

### Prepare Windwalker Database object

``` php
use Windwalker\Database\DatabaseFactory;

// Make the database driver.
$db = DatabaseFactory::getDbo(
    'mysql',
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

The DatabaseDriver will be cache in Factory, now DataMapper will auto load Windwalker DatabaseDriver 
from `DatabaseFactory` and `DatabaseAdapter` to operate DB.

#### Current Supported Drivers

- mysql
- postgresql

Todo:

- oracle
- mssql
- sqlite

### Manually Set An Exists DatabaseDriver To Adapter
 
``` php
use Windwalker\DataMapper\Adapter\DatabaseAdapter;
use Windwalker\DataMapper\Adapter\WindwalkerAdapter;

DatabaseAdapter::setInstance(new WindwalkerAdapter($db));
```

## Create a DataMapper

``` php
use Windwalker\DataMapper\DataMapper;

$fooMapper = new DataMapper('#__foo');

$fooSet = $fooMapper->find(array('id' => 1));
```

### Extend It

You can also create a class to operate specific table:

``` php
class FooMapper extends DataMapper
{
    protected $table = '#__foo';
}

$data = (new FooMapper)->findAll();
```

Or using facade:

``` php
abstract class FooMapper
{
    protected static $instance;
    
    public static function getInstance()
    {
        if (!static::$instance)
        {
            static::$instance = new DataMapper('#__foo');
        }
        
        return static::$instance;
    }
    
    public static function __callStatic($name, $args)
    {
        return call_user_func_array(array(static::getInstance(), $name), $args);
    }
}

$data = FooMapper::findOne(array('id' => 5, 'alias' => 'bar'));
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

Use `RelationDataMapper` to join tables.

``` php
$fooMapper = new RelationDataMapper('flower', '#__flower');

$fooMapper->addTable('author', '#__users', 'flower.user_id = author.id', 'LEFT')
    ->addTable('category', '#__categories', array('category.lft >= flower.lft', 'category.rgt <= flower.rgt'), 'INNER');

// Don't forget add alias on where conditions.
$dataset = $fooMapper->find(array('flower.id' => 5));
```

The Join query will be:

``` sql
SELECT `flower`.`id`,
	`flower`.`catid`,
	`flower`.`title`,
	`flower`.`user_id`,
	`flower`.`meaning`,
	`flower`.`ordering`,
	`flower`.`state`,
	`flower`.`params`,
	`author`.`id` AS `author_id`,
	`author`.`name` AS `author_name`,
	`author`.`pass` AS `author_pass`,
	`category`.`id` AS `category_id`,
	`category`.`title` AS `category_title`,
	`category`.`ordering` AS `category_ordering`,
	`category`.`params` AS `category_params`
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

### Group

``` php
$fooMapper->group('category.id');
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

### Available compares:

| Name       | Description      | Operator |
| ---------- | -----------------| -------- |
| EqCompare  | Equal                 | `=`  |
| NeqCompare | Not Equal             | `!=` |
| GtCompare  | Greater than          | `>`  |
| GteCompare | Greater than or Equal | `>=` |
| LtCompare  | Less than             | `<`  |
| LteCompare | Less than or Equal    | `<=` |
| InCompare  | In                    | `IN` |
| NinCompare | Not In                | `IN` |

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

See: https://github.com/ventoviro/windwalker-data

## Hooks

Add `"windwalker/event": "~2.0"` to `composer.json`.

Then we are able to use hooks after every operations.

``` php
class FooListener
{
    public function onAfterCreate(Event $event)
    {
        $result = $event['result'];

        // Do something
    }
}

$mapper = new DataMapper('table');
$mapper->getDispatcher()->addListener(new FooListener);

$mapper->create($dataset);
```

Extends DataMapper:

``` php
class SakuraMapper extends DataMapper
{
    protected $table = 'saluras';

    public function onAfterFind(Event $event)
    {
        $result = $event['result'];

        // Find some relations
    }
}

$mapper = new DataMapper('table');
$mapper->find(array('id' => 5));
```

More about Event: [Windwalker Event](https://github.com/ventoviro/windwalker-event)

## Integrate Other Framework's DB Object
 
Create your own adapter.
 
``` php
class MyDatabaseAdapter extends DatabaseAdapter
{
    protected $db;

    public function __construct(MyDBObject $db)
    {
        $this->db = $db;
    }

	public function find($table, $select = '*', array $conditions = array(), array $orders = array(), $start = 0, $limit = null) {}
	public function create($table, $data, $pk = null) {}
	public function updateOne($table, $data, array $condFields = array()) {}
	public function updateAll($table, $data, array $conditions = array()) {}
	public function delete($table, array $conditions = array()) {}
	public function getFields($table) {}
	public function transactionStart($asSavePoint = false) {}
	public function transactionCommit($asSavePoint = false) {}
	public function transactionRollback($asSavePoint = false) {}
}

DatabaseAdapter::setInstance(new MyDatabaseAdapter($db));
```

DataMapper will call this adapter to operate DB, it prevents that there may be 2 DB connections exists at the same time. 
