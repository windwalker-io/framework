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

We always get only one DB object, it can make sure we have only one connection at one time.

``` php
// Same as previous DB object
$db = DatabaseFactory::getDbo();
```

Every database driver has one object, this allow us to operate multiple DB connections to different DB services.

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

This is an example of insert data.

``` php
$db = DatabaseFactory::getDbo();

$sql = 'INSERT INTO foo_table (title, state) VALUES ("Flower", 1)';

$db->setQuery($sql);

$db->execute();
```

### Fetch Data From Database

#### loadAll

Using the query we set to load all data as objects, wrap it by an array.

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

// bool(true)
var_dump($items[0] instanceof \Windwalker\Data\Data);
```

We can load records as hash array or associative array.

- `assoc`: Returns an array indexed by column name as returned in your result set
- `array`: Returns an array indexed by column number as returned in your result set, starting at column 0
- See: PHP.net [PDOStatement::fetch](http://php.net/manual/en/pdostatement.fetch.php)

``` php
// Every record will be a hash array, same as PDO::fetch(PDO::FETCH_ASSOC)
$items = $db->loadAll(null, 'array');

// Every record will be key-valued, same as PDO::fetch(PDO::FETCH_NUM)
$items = $db->loadAll(null, 'assoc');
```

Use column values as array index. For example, we can use id as array indexes:

``` php
$items = $db->loadAll('id');

// True
($items[11]->id == 11);
```

#### loadOne

We can only get first record and ignore others:

``` php
$db = DatabaseFactory::getDbo();

$sql = 'SELECT * FROM foo_table WHERE state = 1';

$db->setQuery($sql);

$item = $db->loadOne();
```

The `$item` will be a `stdClass` object or `false` (If no any records found).

We can also use other object class to wrap our data:

``` php
$item = $db->loadAll('Windwalker\\Data\\Data');

// bool(true)
var_dump($item instanceof \Windwalker\Data\Data);
```

Aslo, we can get this data as hash array or associative array:

``` php
// Hash array
$item = $db->loadAll('array');

// Associative array
$item = $db->loadAll('assoc');
```

## Using Reader

Reader is a command object that help us read records from database, this is a simple example to use Reader.

``` php
$reader = $db->getReader();

$items = $reader->setQuery($sql)->loadObjectList();

// OR

$items = $db->getReader($sql)->loadObjectList();
```

### loadObjectList()

Return an array, every element is a record and wrap with an object. This method is same as `$db->loadAll()`, we can set index
 column and object class:

``` php
$reader = $db->getReader($sql);

$items = $reader->loadObjectList();
$items = $reader->loadObjectList('id');
$items = $reader->loadObjectList('id', 'MyObject');
```

### loadObject()

Return only one record and wrap with an object.  This method is same as `$db->loadOne()`, we can set index
column and object class:

``` php
$reader = $db->getReader($sql);

$item = $reader->loadObject();
$item = $reader->loadObject('MyObject');
```



