# Windwalker Record

Windwalker Record is a simple Active Record to operate database row.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/record": "~3.0"
    }
}
```

## Create A Record

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
if (!$user->load(25))
{
    throw new RuntuneException('User not found');
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

### Check

Check method help you validate data.

``` php
class UserRecord extends Record
{
    // ...

    public function check()
    {
        if (!$this['name'])
        {
            throw new InvalidArgumentException('Name empty.');
        }

        return true;
    }
}
```

Then we call `check()` before `store()`.

``` php
$user->bind($data);

$user->check();

$user->store();
```

### Delete

``` php
$user->load(30);
$result = $user->delete(); // boolean

// OR delete by conditions

$result = $user->delete(30); // boolean
$result = $user->delete(array('username' => $username)); // boolean
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
    ->setLocation(1, NestedRecord::POSITION_FIRST_CHILD)
    ->store();
```

Now we will have a new node and it id is `2`. Create a new node as last child of `2`.

``` php
$cat->bind($data)
    ->setLocation(2, NestedRecord::POSITION_LAST_CHILD)
    ->store();
```

Available positions:

- POSITION_FIRST_CHILD
- POSITION_LAST_CHILD
- POSITION_BEFORE
- POSITION_AFTER

### Move Node

#### Re Ordering

``` php
$cat->move(1); // move up
$cat->move(-1); // Move down
```

#### Move To Other Node

Move to node `3` as last child.

``` php
$cat->moveByReference(3, NestedRecord::POSITION_LAST_CHILD);
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
