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

## Execute A Query

This is an example of insert data.

``` php
$db = DatabaseFactory::getDbo();

$sql = 'INSERT INTO foo_table (title, state) VALUES ("Flower", 1)';

$db->setQuery($sql);

$db->execute();
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

Return an array, every element is a record and wrap with an object. This method is same as `$db->loadAll()`:

``` php
$sql = 'SELECT * FROM foo_table WHERE state = 1';

$reader = $db->getReader($sql);

$items = $reader->loadObjectList();
```

The return value is an array contains all records we found, every record will be an `stdClass` object.

We can set object class to store records:

``` php
$items = $reader->loadObjectList(null, 'Windwalker\\Data\\Data');

// bool(true)
var_dump($items[0] instanceof \Windwalker\Data\Data);
```

Use column values as array index. For example, we can use id as array indexes:

``` php
$items = $reader->loadObjectList('id');

// True
($items[11]->id == 11);
```

### loadObject()

Return only one record and wrap with an object.  This method is same as `$db->loadOne()`:

``` php
$sql = 'SELECT * FROM foo_table WHERE id = 1';

$reader = $db->getReader($sql);

$item = $reader->loadObject();
```

The `$item` will be a `stdClass`  object or `false` (If no any records found). we can set object class:

``` php
$item = $reader->loadObject('MyObject');
```

Then the object will be an instance of `MyObject`.

### loadArrayList()

Same as `$db->loadAll('array')`, returns an array and every element is an array indexed by column number as
returned in your result set, starting at column 0, we can set a column as index:

``` php
$reader = $db->getReader($sql);

$items = $reader->loadArrayList();
$items = $reader->loadArrayList('id'); // Use id as index
```

### loadArray()

Returns an array indexed by column number as returned in your result set, starting at column 0:

``` php
$reader = $db->getReader($sql);

$item = $reader->loadArray();
```

### loadAssocList()

Returns an array and every element is an associative array indexed by column name.

``` php
$reader = $db->getReader($sql);

$items = $reader->loadAssocList();
$items = $reader->loadAssocList('id'); // Use id as index
```

### loadAssocList()

Returns an associative array indexed by column name.

``` php
$reader = $db->getReader($sql);

$item = $reader->loadAssoc();
```

### loadColumn()

Fetch values of a column field, please select only one column in this query:

``` php
$titles = $db->getReader('SELECT title FROM article_table')->loadColumn();
```

### loadResult()

Fetch only one cell as a value:

``` php
// Get article id = 3 title
$title = $db->getReader('SELECT title FROM article_table WHERE id = 3')->loadResult();

// Get total hits
$sum = $db->getReader('SELECT SUM(hits) FROM article_table')->loadResult();

// Get a value
$id = $db->getReader('SELECT LAST_INSERT_ID()')->loadResult();
```

> See: [PHP.net / PDOStatement::fetch](http://php.net/manual/en/pdostatement.fetch.php)

### Quick Fetch From Driver

#### DatabaseDriver::loadAll()

Using a query we set into DB object to fetch records.

``` php
$sql = 'SELECT * FROM foo_table WHERE state = 1';

$db->setQuery($sql);

$items = $db->loadAll();

// Same as $reader->loadObjectList('id')
$items = $db->loadAll('id');

// Same as $reader->loadObjectList(null, 'MyObject')
$items = $db->loadAll(null, 'MyObject');

// Same as $reader->loadArrayList()
$items = $db->loadAll(null, 'array');

// Same as $reader->loadAssocList()
$items = $db->loadAll(null, 'assoc');
```

The return value is an array contains all records we found.

#### DatabaseDriver::loadOne()

We can only get first record and ignore others:

``` php
$db->setQuery($sql);

$item = $db->loadOne();

// Same as $reader->loadObject(null, 'MyObject')
$item = $db->loadOne('MyObject');

// Same as $reader->loadArray()
$item = $db->loadOne('array');

// Same as $reader->loadAssoc()
$item = $db->loadOne('assoc');
```

The `$item` will be a record or `false` (If no any records found).

#### DatabaseDriver::loadColumn()

Same as `$db->getReader($sql)->loadColumn()`.

#### DatabaseDriver::loadResult()

Same as `$db->getReader($sql)->loadResult()`.


