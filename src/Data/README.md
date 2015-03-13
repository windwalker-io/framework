# Windwalker Data

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/data": "~2.0"
    }
}
```

## Using Data Object

The constructor of `Data` can insert an array or object, it will convert to Data properties.

``` php
use Windwalker\Data\Data;

$array = array(
    'foo' => 'bar',
    'flower' => 'sakura'
);

$data = new Data($array);

echo $data->flower; // sakura
```

### Binding object into it

``` php
$obj = new \stdClass;
$obj->goo = 'yoo';

$data->bind($obj);

echo $data->goo; // yoo
```

### Get and Set property

Data object has magic method to be getter and setter of any property, we don't need to worry about the property exists or not. Non-exists property will return `null`.

``` php
echo $data->foo; // exists

echo $data->yoo; // Not exists, but no warning, it will return null.
```

We can also using getter and setter:

``` php
$data->set('flower', 'rose');

echo $data->get('flower');
```

### Default Value

If some property not exists, we can get a default value.

``` php
echo $data->get('flower', 'Default value');

// OR

echo $data->flower ? : 'Default Value';
```

### Array Access

Using array access to get property:

``` php
// Set
$data['flower'] = 'Sunflower';

// Get
echo $data['flower'];
```

### Iterator

`Data` object can directly use in foreach as iterator:

``` php
foreach ($data as $key => $value)
{
    echo $key . ' => ' . $value;
}
```

### Null Data

In PHP, an empty object means not empty, so this code will return FALSE:

``` php
$data = new Data; // Data object with no properties

// IS NULL?
var_dump(empty($data)); // bool(false)
```

So we use `isNull()` method to detect whether object is empty or not, this is similar to [Null Object pattern](http://en.wikipedia.org/wiki/Null_Object_pattern):

``` php
$data = new Data;

// IS NULL?
var_dump($data->isNull()); // bool(true)
```

Another simple way is convert it to array, this also work:

``` php
// IS NULL?
var_dump(empty((array) $data)); // bool(true)
```

### Map and Walk

``` php
$data->map(function($value)
{
    return strtoupper($value);
});

$data->walk(function(&$value, $key, $userdata)
{
    $value = $userdata . ':' . $key . ':' . $value;
}, 'prefix');
```


## Using DataSet Object

`DataSet` is a data collection bag for `Data` object. We can insert array with data in constructor.

``` php
use Windwalker\Data\Data;
use Windwalker\Data\DataSet;

$dataSet = new DataSet(
    array(
        new Data(array('id' => 3, 'title' => 'Dog')),
        new Data(array('id' => 4, 'title' => 'Cat')),
    )
);
```

### Array Access

Operate `DataSet` as an array, it use magic method to get and set data.

``` php
echo $dataSet[0]->title; // Dog
```

Push a new element:

``` php
$dataSet[] = new Data(array('id' => 6, 'title' => 'Lion'));
```

### Iterator

We can also using iterator to loop all elements:

``` php
foreach ($dataSet as $data)
{
    echo $data->title;
}
```

### The Batch Getter & Setter

Get values of `foo` field from all data objects.

``` php
// will be an array of every Data's foo property
$value = $dataset->foo;
```

Set value to `bar` field of all object.

``` php
// will set 'Fly' to every Data's bar property
$dataset->bar = 'Fly';
```

### Map and Walk

``` php
$dataset->map(function($data)
{
    $data->foo = 'bar';

    return $data;
});

$dataset->walk(function(&$data, $key, $userdata)
{
    $data->foo = $userdata . ':' . $key;
}, 'prefix');
```
