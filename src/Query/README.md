# Windwalker Query

Windwalker Query package is a query builder object help you organize SQL syntax and provides multi-database syntax support.

This package is a modified version of Joomla DatabaseQuery but add more features.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/query": "~3.0"
    }
}
```

## Create A Query Object

Just create an object for your database.

``` php
use Windwalker\Query\Mysql\MysqlQuery;
use Windwalker\Query\Postgresql\PostgresqlQuery;
use Windwalker\Query\Sqlite\SqliteQuery;

$query = new MysqlQuery;

$query = new PostgresqlQuery;

$query = new SqliteQuery;

$query->select('*')->from('shakespeare')->where('year <= 1616');
```

Available databases:

- Mysql
- Cubrid
- Mariadb
- Oracle
- Postgresql
- Sqlite
- Sqlserv

## Pass PDO Into Query Object

Query object provides `escape()` method to escaping some invalid string, it will do some simple escape when 
there are not a DB driver set into it, but the most safe way to escape string is that using the escaping function 
provided by DB driver, so we should pass a DB driver to query object.

``` php
$query = new MysqlQuery;

// Simple escape, but not safe enough.
$query->escape($_REQUEST['username']);

// Pass PDO into Query
$pdo = new PDO($dsn, $user, $pass);

$query = new MysqlQuery($pdo);
// OR
$query->setConnection($pdo);

// Now it is safe escaping
$query->escape($_REQUEST['username']);
```

If you are bother of pass DB driver every time, push it into `ConnectionContainer`:

``` php
use Windwalker\Query\ConnectionContainer;

// Set PDO into ConnectionContainer, you can set different driver for every database type.
ConnectionContainer::setConnection('mysql', $pdo);

// Now the DB driver will auto inject into mysql Query object.
$query = new MysqlQuery;
```

## A Simple Select

This is a example of simple select syntax with where condition.

``` php
$query->select('*')
    ->from('shakespeare')
    ->where('year <= 1616');
    
echo $query;
```

The result:

``` sql
SELECT *
FROM shakespeare
WHERE year <= 1616
```

### More Select Options

``` php
$query->select(array('title', 'meta', 'read'))
    ->from('shakespeare')
    ->where('year <= 1616')
    ->where('published > 0')
    ->order('popular DESC')
    ->limit(15);
    
echo $query;
```

Result:

``` sql
SELECT title, meta, read
FROM shakespeare
WHERE year <= 1616
    AND published > 0
ORDER BY popular DESC
LIMIT 15
```

### Limit Process

For common use, Mysql and some databases use this limit syntax:

``` sql
LIMIT {limit}
# OR
LIMIT {offset}, {limit}
```

Windwalker Query dose not follow this ordering, the Query::limit method look like: `limit($limit, $offset)`. 
We must use `limit(3, 0)` to generate `LIMIT 0, 3` because it is more semantic on method interface.

### Where Conditions

`where()` method support array as argument.

``` php
$query->select('*')
    ->from('shakespeare')
    ->where(array('year <= 1616', 'published > 0'));
    
echo $query;
```

Result

``` sql
SELECT *
FROM shakespeare
WHERE year <= 1616
    AND published > 0
```

There are many ways you can use `where()` method:

``` php
// Use array
$query->where(array('a = b', 'c = d')); // a = b AND c = d

// Use format
$query->where('%q = %n', 'flower', 'sakura'); // `flower` = 'sakura'

// Use bind
$query->where('`flower` = :name')->bind('name', $name); // `flower` = 'sakura'

// Or bind an array data
$query->where('`flower` = :name')->bind($array); // `flower` = 'sakura'
```

See [Query Format](#format)

`orWhere()`:

``` php
$query->select('*')
    ->from('shakespeare')
    ->where('year <= 1616')
    ->orWhere(array(
        "foo = 'bar'",
        "flower = 'sakura'"
    ));
    
echo $query;
```

Result

``` sql
SELECT *
FROM shakespeare
WHERE year <= 1616
    AND (foo = 'bar' OR flower = 'sakura')
```

Build where by callback:

``` php
$query->orWhere(function (Query $query)
{
    $query->where("foo = 'bar'")
		->where("flower = 'sakura'");
});
```

You can use `QueryElement` to create an `()` element:

``` php
echo $query->element('()', $conditions, ' OR ');

// Result also: '(foo = 'bar' OR flower = 'sakura')'
```

### Quote Table And Column Name

Sometimes we may use the reserve word of SQL, so we have to quote it to make sure syntax correct.

``` php
$query->select('*')
    ->from($query->quoteName('shakespeare'))
    ->where($query->qn('year') . ' <= 1616'); // qn() is alias of quoteName
    
echo $query;
```

Result

``` sql
SELECT *
FROM `shakespeare`
WHERE `year` <= 1616
```

Quote name and alias

``` php
$query->quoteName('a.title AS a_title'); // `a`.`title` AS `a_title`
```

### Quote String

using `quote()` to quote normal string and [escape](#pass-pdo-into-query-object) it.

``` php
$query->select('*')
    ->from($query->quoteName('shakespeare'))
    ->where($query->qn('year') . ' <= 1616')
    ->where($query->qn('foo') . ' = ' . $query->quote('bar'));
    ->where($query->qn('Nick_Fury') . ' = ' . $query->q("You think you're the only hero?")); // q() is alias of quote()
    
echo $query;
```

Result

``` sql
SELECT *
FROM `shakespeare`
WHERE `year` <= 1616
    AND `foo` = 'bar'
    AND `Nick_Fury` = 'You think you\'re the only hero?'
```

Quote array

``` php
$query->quote(array(1, 2, 3)); // array("'1'", "'2'", "'3'")
```

## Join

``` php
$query->select('a.*, b.*')
    ->from('shakespeare AS a')
    ->join('LEFT', 'libraries AS b', 'a.id = b.work_id')
    ->where('a.year <= 1616');
    
echo $query
```

Result

``` sql
SELECT a.*, b.*
    FROM shakespeare AS a
    LEFT JOIN libraries AS b ON a.id = b.work_id
    WHERE a.year <= 1616
```

Multi-conditions

``` php
$query->join('LEFT', 'libraries AS b', array('a.id = b.work_id', 'a.foo < b.bar')); // Will be AND

$query->join('LEFT', 'libraries AS b', 'a.id = b.work_id OR a.foo < b.bar');
```

Support Join Type:

- LEFT
- RIGHT
- INNER
- OUTER

## Insert

``` php
$query->insert('shakespeare')
    ->columns(array('title', 'year'))
    ->values("'The Tragedy of Julius Caesar', 1599")
    ->values("'Macbeth', 1606");
    
echo $query;
```

Result

``` sql
INSERT INTO shakespeare 
(title, year) 
VALUES 
('The Tragedy of Julius Caesar', 1599),
('Macbeth', 1606)
```

Values can be array

``` php
$query->insert('shakespeare')
    ->columns(array('title', 'year'))
    ->values(
        array(
            "'The Tragedy of Julius Caesar', 1599",
            "'Macbeth', 1606"
        )
    );
    
// OR

$query->insert('shakespeare')
    ->columns(array('title', 'year'))
    ->values(
        array(
            $query->q(array("The Tragedy of Julius Caesar", "1599")),
            $query->q(array("Macbeth", "1606"))
        )
    );
```

## Update

``` php
$query->update('shakespeare')
    ->set('modified = "2014-10-09"')
    ->set('version = version + 1')
    ->where('year <= 1616');
    
echo $query;
```

Result

``` sql
UPDATE shakespeare
SET 
    modified = "2014-10-09", 
    version = version + 1
WHERE year <= 1616
```

Use array

``` php
$query->set(array('modified = "2014-10-09"', 'version = version + 1'));
```

## Delete

``` php
$query->delete('shakespeare')
    ->where('year > 1616');
```

Result

``` sql
DELETE shakespeare WHERE year > 1616
```

## Format

``` php
echo $query->format('%n = %q', 'title', 'Caesar'); // `title` = 'Caesar'
```

Find and replace sprintf-like tokens in a format string.
Each token takes one of the following forms:

    %%       - A literal percent character.
    %[t]     - Where [t] is a type specifier.
    %[n]$[x] - Where [n] is an argument specifier and [t] is a type specifier.

### Types:

    a - Numeric: Replacement text is coerced to a numeric type but not quoted or escaped.
    e - Escape: Replacement text is passed to $this->escape().
    E - Escape (extra): Replacement text is passed to $this->escape() with true as the second argument.
    n - Name Quote: Replacement text is passed to $this->quoteName().
    q - Quote: Replacement text is passed to $this->quote().
    Q - Quote (no escape): Replacement text is passed to $this->quote() with false as the second argument.
    r - Raw: Replacement text is used as-is. (Be careful)

### Date Types:

- Replacement text automatically quoted (use uppercase for Name Quote).
- Replacement text should be a string in date format or name of a date column.

```
y/Y - Year
m/M - Month
d/D - Day
h/H - Hour
i/I - Minute
s/S - Second
```

### Invariable Types:

- Takes no argument.
- Argument index not incremented.

```
t - Replacement text is the result of $this->currentTimestamp().
z - Replacement text is the result of $this->nullDate(false).
Z - Replacement text is the result of $this->nullDate(true).
```

### Usage:

``` php
$query->format('SELECT %1$n FROM %2$n WHERE %3$n = %4$a', 'foo', '#__foo', 'bar', 1);

//Returns: SELECT `foo` FROM `#__foo` WHERE `bar` = 1
```

### Notes:

The argument specifier is optional but recommended for clarity.
The argument index used for unspecified tokens is incremented only when used.

## Bind Params

We can bind params to our query string:
 
``` php
// Bind data
$query->where('title = :title')
    ->bind(':title', 'Hamlet');

// Now do execute of this query
$bounded =& $query->getBounded();

foreach ($bounded as $key => $data)
{
    $pdo->bindParam($key, $data->value, $data->dataType, $data->length, $data->driverOptions);
}

// Or use Windwalker Database
$db->setQuery($query)->loadAll();
```

## Query Expression

An easy way to build expression or function syntax.

``` php
echo $query->expression('FUNCTION', 'a', 'b', 'c'); 

// FUNCTION(a, b, c)
```

The benefit to using `expression()` is that it will auto fit different databases.

``` php
$mysqlQuery->expression('CONCAT', array('a', 'b', 'c'));

// CONCAT(a, b, c)

$mysqlQuery->expression('CONCAT', array('a', 'b', 'c'), "';'");

// CONCAT_WS(';', a, b, c)

$sqliteQuery->expression('CONCAT', array('a', 'b', 'c'));

// CONCATENATE(a || b || c)

$sqliteQuery->expression('CONCAT', array('a', 'b', 'c'), "';'");

// CONCATENATE(a || ';' || b || ';' || c)
```

Short alias

``` php
echo $query->expr('FUNCTION', 'a', 'b', 'c'); 
```

## Query Element

Help you build a value list:

``` php
echo new QueryElement('WHERE', array('a = b', 'c = d'), ' OR ');

// WHERE a = b OR c = d
```

``` php
echo new QueryElement('()', array('a = b', 'c = d'), ' OR ');

// (a = b OR c = d)
```

``` php
echo new QueryElement('IN()', array(1, 2, 3, 4));

// IN(1, 2, 3, 4)
```
