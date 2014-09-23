# Database Command

How to get Database Command:

``` php
$database = $db->getDatabase('flower');
```

## create()

Create a new database.

``` php
$database = $db->getDatabase('flower');

$database->create();
```

## drop()

Drop a database.

``` php
$database = $db->getDatabase('flower');

$database->drop();
```

## rename()

Rename a database.

``` php
$database = $db->getDatabase('flower');

// The return value is a new command object
$newDatabaseCommand = $database->rename('flower2');

$newDatabaseCommand->getName(); // flower2
```

## getTables()

Get table name list.

``` php
$database = $db->getDatabase('flower');

$tables = $database->getTables();
```

## getTableDetail() & getTableDetails()

Get tables information detail.
