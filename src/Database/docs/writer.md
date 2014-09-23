# Writer Command

Writer object provide us an interface to write data into database.

## insert()

Using an object or array to store data into a table, argument 3 is the name of primary key that will be added
to data object if insert success:

``` php
$data = array(
    'title' => 'Sakura',
    'created' => '2014-03-02'
);

$db->getWriter()->insert('#__articles', $data, 'id');

// $data['id'] will be the last inserted id
echo $data['id'];

// OR using object

$data = new stdClass;

$data->title = 'Sakura';
$data->created = '2014-03-02';

$db->getWriter()->insert('#__articles', $data, 'id');

// $data->id will be the last inserted id
echo $data->id;
```

## insertMultiple()

Insert many records:

``` php
$dataSet = array(
    array(
        'title' => 'Sakura',
        'created' => '2014-03-02'
    ),
    array(
        'title' => 'Sunflower',
        'created' => '2014-05-02'
    );
);

$db->getWriter()->insertMultiple('#__articles', $dataSet, 'id');

// $dataSet[0]['id'] will be the last inserted id
echo $dataSet[0]['id'];
```

## update()

Using an object or array to update a record into a table, argument 3 is the where key value that we added to query:

``` php
$data = new stdClass;

$data->id = 1;
$data->title = 'Sakura2';

$db->getWriter()->update('#__articles', $data, 'id');

// Same as `UPDATE #__articles SET title = "Sakura2" WHERE id = 1;`
```

Also we can use array instead of object as data.

## updateMultiple()

Same as `update()` but update every record in an array.

``` php
$dataSet = array(
    $data1,
    $data2
);

$db->getWriter()->updateMultiple('#__articles', $dataSet, 'id');
```

## updateBatch()

Using where conditions to update some values to every records which matched this condition.

``` php
$data = new stdClass;

$data->state = 0;

// Update all author=13 records to state 0, same as `UPDATE #__articles SET state = 0 WHERE author = 15;`
$db->getWriter()->updateBatch('#__articles', $data, array('author' => 15));
```

Using other conditions:

``` php
$conditions = array(
    'author' => 15,
    'updated < "2014-03-02"',
    'catid' => array(1, 2, 3)
);

$db->getWriter()->updateBatch('#__articles', $data, $conditions);

// Same as `UPDATE #__articles SET state = 0 WHERE author = 15 AND updated < "2014-03-02" AND catid IN(1, 2, 3);`
```

## Save & SaveMultiple

`save()` and `saveMultiple()` will auto check the primary exists or not. If primary key exists, it will use `update`,
if not exists, it will use `insert` to store data.

## insertId()

Get the last inserted id.

## countAffected()

Count the affected rows of last query.
