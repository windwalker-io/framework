# Query Conditions

The `QueryHelper::buildWheres()` provides a powerful array to query WHERE conditions builder.

## Simple Equals Condition

``` php
use Windwalker\Database\Query\QueryHelper;
use Windwalker\Query\Mysql\MysqlQuery;

$query = new MysqlQuery;
$query->select('*')->from('table');

echo QueryHelper::buildWheres($query, array('id' => 5));
```

Output:

``` sql
SELECT * FROM table WHERE `id` = '5'
```

## Custom Conditions

If key is numeric, the array element value will be where condition.

``` php
echo QueryHelper::buildWheres($query, array(
	'state > 1',
	'category_id <= 5'
));
```

Output:

``` sql
SELECT * FROM table WHERE state > 1 AND category_id <= 5
```

## IN Condition

Use array to be `IN()` condition.

``` php
echo QueryHelper::buildWheres($query, array(
	'state > 1',
	'category_id' => array(1, 2, 3, 4)
));
```

Output:

``` sql
SELECT * FROM table WHERE state > 1 AND `category_id` IN ('1','2','3','4')
```

## Expressions

``` php
echo QueryHelper::buildWheres($query, array(
	'state > 1',
	'category_id = ' . $query->expression('rand')
));
```

Output:

``` sql
SELECT * FROM table WHERE state > 1 AND category_id = RAND()
```

## Allow NULL

By default, NULL value will be ignore, but we can allow it in 3rd argument.

``` php
echo QueryHelper::buildWheres($query, array(
	'state > 1',
	'category_id' => null
), true);
```

Output:

``` sql
SELECT * FROM table WHERE state > 1 AND `category_id` = NULL
```


