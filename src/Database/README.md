# Windwalker Database Package

Windwalker database package is a DB operation wrapper, provide us an advanced way to access database and store data.

## Installation via Composer

Add this dependency in your `composer.json` file.

``` json
{
    "require": {
        "windwalker/database" : "~2.0"
    }
}
```

## Getting Started

### Create A DatabaseDriver

``` php
use Windwalker\Database\DatabaseFactory;

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

### Using My Connection Resource

If you are already have a DB connection, you can pass it into Windwalker DB object that we can make sure there are
only one connection at one time:

``` php
// Create your own DB connection
$pdo = new PDO($dsn, $user, $pass);

// Add it to options resource index
$options = array(
    'resource' => $pdo
);

$db = DatabaseFactory::getDbo('mysql', $options);

// bool(true)
var_dump($db->getConnection() === $pdo);
```

Also, you can set connection at runtime:

``` php
$db->setConnection($resource);
```

The other way:

``` php
use Windwalker\Database\Driver\Mysql\MysqlDriver;

$db = new MysqlDriver($pdo);

DatabaseFactory::setDbo('mysql', $db);
```

## Execute A Query

This is an example of insert data.

``` php
$db = DatabaseFactory::getDbo();

$sql = 'INSERT INTO foo_table (title, state) VALUES ("Flower", 1)';

$db->setQuery($sql);

$db->execute();
```

## Fetch records

### Fetch multiple rows

This will fetch multiple rows from table, and every record will be an object.

``` php
$sql = 'SELECT * FROM foo_table WHERE state = 1';

$db->setQuery($sql);

$items = $db->loadAll();
```

Customize:

``` php
// Custom object class
$items = $db->loadAll(null, 'MyObject');

// Record as array with number as indexes
$items = $db->loadAll(null, 'array');

// Record as array with column name as indexes
$items = $db->loadAll(null, 'assoc');

// Use id column as $items index
$items = $db->loadAll('id', 'assoc');
```

### Fetch one row

``` php
$sql = 'SELECT * FROM foo_table WHERE id = 3';

$db->setQuery($sql);

$item = $db->loadOne();

// Custom object class
$items = $db->loadAll('MyObject');

// Record as array with number as indexes
$items = $db->loadAll('array');

// Record as array with column name as indexes
$items = $db->loadAll('assoc');
```

## Table Prefix

Add `prefix` in options when you create DB object, then DB object will auto replace all `#__` with prefix in every query:

``` php
$options = array(
    'host'     => 'localhost',
    'user'     => 'db_user',
    'password' => 'db_pass',
    'database' => 'my_dbname',
    'prefix'   => 'foo_'
);

$db = DatabaseFactory::getDbo('mysql', $options);

$items = $db->setQuery('SELECT * FROM #__articles')->loadAll();

// The query will be `SELECT * FROM foo_articles`
```

## Iterating Over Results

``` php
$iterator = $db->setQuery('SELECT * FROM #__articles WHERE state = 1')->getIterator();

foreach ($iterator as $row)
{
    // Deal with $row
}
```

It allows also to count the results.

``` php
$count = count($iterator);
```

## Using Command

Database Command is some powerful tool set help us operate database, here is commands documents:

- [Reader Command](docs/reader.md)
- [Writer Command](docs/writer.md)
- [Transaction Command](docs/transaction.md)
- [Database Command](docs/database.md)
- [Table Command](docs/table.md)

## Logging

`Database\DatabaseDriver` implements the `Psr\Log\LoggerAwareInterface` so is ready for intergrating with a logging package that supports that standard.

Drivers log all errors with a log level of `LogLevel::ERROR`.

If debugging is enabled (using `setDebug(true)`), all queries are logged with a log level of `LogLevel::DEBUG`. The context of the log include:

* **sql** : The query that was executed.
* **category** : A value of "databasequery" is used.

### An example to log error by Monolog

Add this to `composer.json` require block.

``` json
"monolog/monolog" : "1.*"
```

Then we push Monolog into Database instance.

``` php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

// Create logger object
$logger = new Logger('sql');

// Push logger handler, use DEBUG level that we can log all information
$logger->pushHandler(new StreamHandler('path/to/log/sql.log', Logger::DEBUG));

// Use PSR-3 logger processor that we can replace {sql} with context like array('sql' => 'XXX')
$logger->pushProcessor(new PsrLogMessageProcessor);

// Push into DB
$db->setLogger($logger);
$db->setDebug(true);

// Do something
$db->setQuery('A WRONG QUERY')->execute();
```

This is the log file:

```
[2014-07-29 07:25:22] sql.DEBUG: A WRONG QUERY {"sql":"A WRONG QUERY","category":"databasequery","trace":[...]} []
[2014-07-29 07:36:01] sql.ERROR: Database query failed (error #42000): SQL: 42000, 1064, You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'A WRONG QUERY' at line 1 {"code":42000,"message":"SQL: 42000, 1064, You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'A WRONG QUERY' at line 1"} []
```

