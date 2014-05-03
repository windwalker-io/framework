# Windwalker Data Package

## Installation via Composer

Add `"ventoviro/windwalker-data": "1.0.*"` to the require block in your composer.json.

``` json
{
    "require": {
        "ventoviro/windwalker-data": "1.0.*"
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

### Binding dat into it

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

echo $data->yoo; // Not exists, but no error, it will return null.
```

We can also using normal getter and setter:

``` php
$data->set('flower', 'rose');

echo $data->get('flower');
```

### Default Value

If some property not exists, we can get a default value.

``` php
echo $data->get('flower', 'Default value');

// OR

echo $data->flower ?: 'Default Value';
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

In PHP, an empty object means exists, so this code will return FALSE:

``` php
$data = new Data; // Empty Data object

// IS NULL?
if (empty($data))
{
    echo 'TRUE';
}
else
{
    echo 'FALSE';
}
```

So we use `isNull()` method to detect whether object is empty or not, this is similar to [Null Object pattern](http://en.wikipedia.org/wiki/Null_Object_pattern):

``` php
$data = new Data;

// IS NULL?
if ($data->isNull())
{
    echo 'TRUE';
}
else
{
    echo 'FALSE';
}
```

Another simple way is convert it to array, this also work:

``` php
// IS NULL?
if (!(array) $data)
{
    echo 'TRUE';
}
else
{
    echo 'FALSE';
}
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

We can operate `DataSet` as an array, it use magic method to get and set data.

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

Get values of `foo` field from all objects.

``` php
$value = $dataset->foo;
```

Set value to `bar` field of all object.

``` php
$dataset->bar = 'Fly';
```
