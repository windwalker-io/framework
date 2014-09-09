# Windwalker Database Package

## Installation via Composer

Add this dependency in your `composer.json` file.

``` json
{
    "require": {
        "ventoviro/windwalker-database" : "2.0.*"
    }
}
```

> Note: Before stable version released, you have to use `dev-master` as the version, and make sure the `minimum-stability` is `dev`.

## Getting Started

Windwalker database package is a DB operation wrapper, provide us an advanced way to access database and store data.

### Create A DatabaseDriver

``` php
$options = array(
    'host'     => 'localhost',
    'user'     => 'db_user',
    'password' => 'db_pass',
    'port'     => 3306, // Optional attribute
    'database' => 'my_dbname',
);

// Use Factory to create DB object
$db = DatabaseFactory::getDbo('mysql', $options);

// Now we can access database
$items = $db->setQuery('SELECT * FROM `foo`')->loadAll();
```

### DB Object is Singleton

We can always get only one DB object, it can make sure we have only one connection at one time.

``` php
// Same as previous DB object
$db = DatabaseFactory::getDbo();
```

But every database driver can has one object, this allow us to operate multiple DB connections to different DB services.

``` php
// Get other driver
$sqlite = DatabaseFactory::getDbo('sqlite', $sqliteOptions);

// Still get MySQL driver because the first created DB object will set as default
$mysql = DatabaseFactory::getDbo();

// Use driver name to get sqlite driver, $sqlite2 object is same as $sqlite
$sqlite2 = DatabaseFactory::getDbo('sqlite');
```

### Set Default Dbo

If we want to change default Dbo to Sqlite, just do this:

``` php
$sqlite = DatabaseFactory::getDbo('sqlite');
DatabaseFactory::setDefaultDbo($sqlite);
```

## Simple Query Access

### Execute A Query

``` php
$db = DatabaseFactory::getDbo();

$sql = 'INSERT INTO foo_table (title, state) VALUES ("Flower", 1)';

$db->setQuery($sql);

$db->execute();
```

### Fetch Data From Database

#### loadAll

Load all data as objects.

``` php
$db = DatabaseFactory::getDbo();

$sql = 'SELECT * FROM foo_table WHERE state = 1';

$db->setQuery($sql);

$items = $db->loadAll();
```

The return value is an array contains all records we found, every record will be an `stdClass` object.

We can set object class to store records:

``` php
$items = $db->loadAll(null, 'Windwalker\\Data\\Data');

// This value will be bool(true)
var_dump($items[0] instanceof \Windwalker\Data\Data);
```

We can load records as hash array or associative array:

``` php
// Every record will be a hash array
$items = $db->loadAll(null, 'array');

// Every record will be key-valued:
$items = $db->loadAll(null, 'assoc');
```

Use a column values as array index. For example, we can use id as array indexes:

``` php
$items = $db->loadAll('id');

// True
$items[11]->id == 11;
```

#### loadOne

We can only get first record and ignore others:

``` php
$db = DatabaseFactory::getDbo();

$sql = 'SELECT * FROM foo_table WHERE state = 1';

$db->setQuery($sql);

$item = $db->loadOne();
```

The `$item` will be a `stdClass` object or `false` (If not found any records).

We can also use other object class:

``` php
$item = $db->loadAll('Windwalker\\Data\\Data');

// This value will be bool(true)
var_dump($item instanceof \Windwalker\Data\Data);
```

