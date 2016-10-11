# Windwalker Record

Windwalker Record is a simple ActiveRecord to operate database row.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/record": "~3.0"
    }
}
```

## Use Record

New a instance.

``` php
use Windwalker\Record\Record;

// Record object for users table
$user = new Record('users');
```

Or create a class:

``` php
use Windwalker\Record\Record;

class UserRecord extends Record
{
    protected $table = 'users';

    protected $keys = 'id';
}

$user = new UserRecord;
```

### Load A Record

``` php
$user->load(25); // Load by primary key

$user->load(array('alias' => $alias)); // Load by field name.
```

Check row exists

``` php
try
{
	$record->load(25);
}
catch (NoResultException $e)
{
	// Handle error
}
```

### Bind Data

``` php
$data = array(
    'name'     => 'Sakura',
    'username' => 'sakura',
    'alias'    => 'sakura',
    'password' => '1234',
    'desc'     => 'foo bar.'
);

$user->bind($data);

$user->name; // Sakura
```

If we have a table with only 3 columns:

| Name |
| ---- |
| name |
| username |
| password |

The fields which not in this table will be remove after binding data.

``` php
$user->alias; // null
```

That makes our fields in Record will always same as DB table.

### Store

#### Create A New Row

If primary not exists, Record will create a new row in table.

``` php
$data = array(
    'name'     => 'Sakura',
    'username' => 'sakura',
    'password' => '1234'
);

$user->bind($data);

$user->store();

echo $user->id; // Auto generated id
```

#### Update A Existing Row

If primary key exists, Record will update it.

``` php
$data = array(
    'id'       => 30,
    'name'     => 'Sakura',
    'username' => 'sakura',
    'password' => '1234'
);

$user->bind($data);

$user->store();
```

### Validate

Check method help you validate data.

``` php
class UserRecord extends Record
{
    // ...

    public function validate()
    {
        if (!$this['name'])
        {
            throw new InvalidArgumentException('Name empty.');
        }

        return true;
    }
}
```

Then we call `validate()` before `store()`.

``` php
$user->bind($data)
    ->validate()
    ->store();
```

### Delete

``` php
$user->load(30);
$result = $user->delete(); // boolean

// OR delete by conditions

$result = $user->delete(30); // boolean
$result = $user->delete(array('username' => $username)); // boolean
```

### Mutator and Accessor

Mutator and accessor is a setter and getter to do some extra modification when you access value via magic methods.

This is an example of mutator:

``` php
class ArticleRecord extends Record
{
    protected function setCreatedDateValue($value)
    {
        if ($value instanceof \DateTime)
        {
            $value = $value->format('Y-m-d H:i:s');
        }

        $this->data['created_date'] = $value;
    }
}
```

Use camel-case style to define a method, then when you access the `created_date` field, this method will
 be auto executed.

``` php
$articleRecord->created_date = new \DateTime('now');

echo $articleRecord->created_date; // 2016-03-02 12:30:29
```

And an example of accessor:

``` php
class ArticleRecord extends Record
{
    protected function getCreatedDateValue($value)
    {
        return new \DateTime($value);
    }
}
```

And now you can get `DateTime` object back:

``` php
echo $articleRecord->created_date->format('Y-m-d H:i:s'); // 2016-03-02 12:30:29
```

## NestedRecord

NestedRecord is a tool help us handle [Nested Set Model](http://en.wikipedia.org/wiki/Nested_set_model).

### Create Table

Name: `categories`

| Name | Type | Description | Need For NestedRecord |
| ---- | ---- | ----------- | --------------------- |
| id | int | Primary Key |  |
| parent_id | int | ID of Parent Node | V |
| title | varchar | Category title |  |
| path | varchar | Node path | V |
| lft | int | Left key | V |
| rgt | int | Right key | V |
| level | int | Node level | V |

### Initialise

Every nested set should have a root node.

``` php
$cat = new NestedRecord('categories');

$cat->createRoot();
```

NOTE: The root node id is `1`.

### Create Node

Set as first child of ROOT

``` php
$cat->bind($data)
    ->setLocation(1, NestedRecord::LOCATION_FIRST_CHILD)
    ->store();
```

Now we will have a new node and it id is `2`. Create a new node as last child of `2`.

``` php
$cat->bind($data)
    ->setLocation(2, NestedRecord::LOCATION_LAST_CHILD)
    ->store();
```

Available positions:

- LOCATION_FIRST_CHILD
- LOCATION_LAST_CHILD
- LOCATION_BEFORE
- LOCATION_AFTER

### Move Node

#### Re Ordering

``` php
$cat->move(1); // move up
$cat->move(-1); // Move down
```

#### Move To Other Node

Move to node `3` as last child.

``` php
$cat->moveByReference(3, NestedRecord::LOCATION_LAST_CHILD);
```

### Rebuild

If a tree was not correct, using rebuild to reset all `lft`, `rgt` of this branch.

``` php
$cat->load(5);
$cat->rebuild(); // Rebuild node: 5 and it's children.
```

### getPath

Method to get an array of nodes from a given node to its root.

``` php
$path = $cat->getPath();
```

### getTree

Method to get a node and all its child nodes.

``` php
$records = $cat->getTree();
```

## Event

Record has an event system that we can process logic before & after every DB operation.

Add event methods in your Record class.

``` php
class UserRecord extends Record
{
	public function onAfterLoad(Event $event)
	{
		$this->foo = array('a', 'b', 'c');
	}
}
```

Or add listeners to Dispatcher (You must install `windwalker/event` first).

``` php
// Use listener object
$record->getDispatcher()->addListener(new MyRecordListener);

// Use callback
$record->getDispatcher()->listen('onAfterStore', function (Event $event)
{
    // Process your logic
});
```

Available events:

- onBeforeLoad
- onAfterLoad
- onBeforeStore
- onAfterStore
- onBeforeDelete
- onAfterDelete
- onBeforeBind
- onAfterBind
- onBeforeCreate
- onAfterCreate
- onBeforeUpdate
- onAfterUpdate
