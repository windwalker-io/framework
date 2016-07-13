# Windwalker Utilities

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/utilities": "~3.0"
    }
}
```

## Using ArrayHelper

### toObject

```php
use Windwalker\Utilities\ArrayHelper;

class Book {
    public $name;
    public $author;
    public $genre;
    public $rating;
}
class Author {
    public $name;
    public $born;
}
$input = array(
    "name" => "The Hitchhiker's Guide to the Galaxy",
    "author" => array(
        "name" => "Douglas Adams",
        "born" => 1952,
        "died" => 2001),
    "genre" => "comic science fiction",
    "rating" => 10
);
$book = ArrayHelper::toObject($input, 'Book');
var_dump($book);
```
Result:
```
class Book#1 (4) {
  public $name =>
  string(36) "The Hitchhiker's Guide to the Galaxy"
  public $author =>
  class Book#2 (6) {
    public $name =>
    string(13) "Douglas Adams"
    public $author =>
    NULL
    public $genre =>
    NULL
    public $rating =>
    NULL
    public $born =>
    int(1952)
    public $died =>
    int(2001)
  }
  public $genre =>
  string(21) "comic science fiction"
  public $rating =>
  int(10)
}
```

### getColumn

```php
use Windwalker\Utilities\ArrayHelper;

$rows = array(
    array("name" => "John", "age" => 20),
    array("name" => "Alex", "age" => 35),
    array("name" => "Sarah", "age" => 27)
);
$names = ArrayHelper::getColumn($rows, 'name');
var_dump($names);
```
Result:
```
array(3) {
  [0] =>
  string(4) "John"
  [1] =>
  string(4) "Alex"
  [2] =>
  string(5) "Sarah"
}
```

### getValue
```php
use Windwalker\Utilities\ArrayHelper;

$city = array(
    "name" => "Oslo",
    "country" => "Norway"
);

// Prints 'Oslo'
echo ArrayHelper::getValue($city, 'name');

// Prints 'unknown mayor' (no 'mayor' key is found in the array)
echo ArrayHelper::getValue($city, 'mayor', 'unknown mayor');
```

### invert

```php
use Windwalker\Utilities\ArrayHelper;

$input = array(
    'New' => array('1000', '1500', '1750'),
    'Used' => array('3000', '4000', '5000', '6000')
);
$output = ArrayHelper::invert($input);
var_dump($output);
```
Result:
```
array(7) {
  [1000] =>
  string(3) "New"
  [1500] =>
  string(3) "New"
  [1750] =>
  string(3) "New"
  [3000] =>
  string(4) "Used"
  [4000] =>
  string(4) "Used"
  [5000] =>
  string(4) "Used"
  [6000] =>
  string(4) "Used"
}
```


### isAssociative

```php
use Windwalker\Utilities\ArrayHelper;

$user = array("id" => 46, "name" => "John");
echo ArrayHelper::isAssociative($user) ? 'true' : 'false'; // true

$letters = array("a", "b", "c");
echo ArrayHelper::isAssociative($letters) ? 'true' : 'false'; // false
```

### group

```php
use Windwalker\Utilities\ArrayHelper;

$movies = array(
    array('year' => 1972, 'title' => 'The Godfather'),
    array('year' => 2000, 'title' => 'Gladiator'),
    array('year' => 2000, 'title' => 'Memento'),
    array('year' => 1964, 'title' => 'Dr. Strangelove')
);
$pivoted = ArrayHelper::pivot($movies, 'year');
var_dump($pivoted);
```
Result:
```
array(3) {
  [1972] =>
  array(2) {
    'year' =>
    int(1972)
    'title' =>
    string(13) "The Godfather"
  }
  [2000] =>
  array(2) {
    [0] =>
    array(2) {
      'year' =>
      int(2000)
      'title' =>
      string(9) "Gladiator"
    }
    [1] =>
    array(2) {
      'year' =>
      int(2000)
      'title' =>
      string(7) "Memento"
    }
  }
  [1964] =>
  array(2) {
    'year' =>
    int(1964)
    'title' =>
    string(15) "Dr. Strangelove"
  }
}
```

### sortObjects

```php
use Windwalker\Utilities\ArrayHelper;

$members = array(
    (object) array('first_name' => 'Carl', 'last_name' => 'Hopkins'),
    (object) array('first_name' => 'Lisa', 'last_name' => 'Smith'),
    (object) array('first_name' => 'Julia', 'last_name' => 'Adams')
);
$sorted = ArrayHelper::sortObjects($members, 'last_name', 1);
var_dump($sorted);
```
Result:
```
array(3) {
  [0] =>
  class stdClass#3 (2) {
    public $first_name =>
    string(5) "Julia"
    public $last_name =>
    string(5) "Adams"
  }
  [1] =>
  class stdClass#1 (2) {
    public $first_name =>
    string(4) "Carl"
    public $last_name =>
    string(7) "Hopkins"
  }
  [2] =>
  class stdClass#2 (2) {
    public $first_name =>
    string(4) "Lisa"
    public $last_name =>
    string(5) "Smith"
  }
}
```

### arrayUnique
```php
use Windwalker\Utilities\ArrayHelper;

$names = array(
    array("first_name" => "John", "last_name" => "Adams"),
    array("first_name" => "John", "last_name" => "Adams"),
    array("first_name" => "John", "last_name" => "Smith"),
    array("first_name" => "Sam", "last_name" => "Smith")
);
$unique = ArrayHelper::arrayUnique($names);
var_dump($unique);
```
Result:
```
array(3) {
  [0] =>
  array(2) {
    'first_name' =>
    string(4) "John"
    'last_name' =>
    string(5) "Adams"
  }
  [2] =>
  array(2) {
    'first_name' =>
    string(4) "John"
    'last_name' =>
    string(5) "Smith"
  }
  [3] =>
  array(2) {
    'first_name' =>
    string(3) "Sam"
    'last_name' =>
    string(5) "Smith"
  }
}
```

### flatten

``` php
use Windwalker\Utilities\ArrayHelper;

$array = array(
    'flower' => array(
        'sakura' => 'samurai',
        'olive' => 'peace'
    )
);

// Make nested data flatten and separate by dot (".")

$flatted1 = ArrayHelper::flatten($array);

echo $flatted1['flower.sakura']; // 'samuari'

// Custom separator

$flatted2 = ArrayHelper::flatten($array, '/');

echo $flatted2['flower/olive']; // 'peace'
```

### merge

Recursive merge two array.

``` php
use Windwalker\Utilities\ArrayHelper;

$array2 = array(
    'flower' => array(
        'sakura' => 'samurai',
        'olive' => 'peace'
    )
);

$array2 = array(
    'flower' => array(
        'sakura' => 'overrided',
    )
);

$newArray = ArrayHelper:merge($array1, $array2, true);

Array
(
    [flower] => Array
    (
        [sakura] => overrided
        [olive] => peace
    )
)
```

### match

Check an array matched our query condition.

``` php
$array = array(
    'id' => 1,
    'title' => 'Julius Caesar',
    'data' => (object) array('foo' => 'bar'),
);

// Check id=1
$bool = ArrayHelper::match($data, array('id' => 1));

// Check id=1 with strict compare (int)
$bool = ArrayHelper::match($data, array('id' => 1), true);

// Check id=1 AND title='Julius Caesar'
$bool = ArrayHelper::match($data, array('id' => 1, 'title' => 'Julius Caesar'));

// Check id IN (2,3,4)
$bool = ArrayHelper::match($data, array('id' => array(2, 3, 4)));

// Check id >= 2
$bool = ArrayHelper::match($data, array('id >=' => 2));
```

### query

Find element matched array in array.

``` php
$data = array(
    array(
        'id' => 1,
        'title' => 'Julius Caesar',
        'data' => (object) array('foo' => 'bar'),
    ),
    array(
        'id' => 2,
        'title' => 'Macbeth',
        'data' => array(),
    ),
    array(
        'id' => 3,
        'title' => 'Othello',
        'data' => 123,
    ),
    array(
        'id' => 4,
        'title' => 'Hamlet',
        'data' => true,
    ),
);

// Query id=2
$newArray = ArrayHelper::query($data, array('id' => 2));

// Query id=2, use strict compare to check is int, not string
$newArray = ArrayHelper::query($data, array('id' => 2), true, [keepKey = false]);

// Query id=2 AND title='Macbeth'
$newArray = ArrayHelper::query($data, array('id' => 2, 'title' => 'Macbeth'));

// Query id IN (2,3,4)
$newArray = ArrayHelper::query($data, array('id' => array(2, 3, 4)));

// Query id >= 2
$newArray = ArrayHelper::query($data, array('id >=' => 2));

// Use callback, similar to array_filter()
$newArray = ArrayHelper::query($data, function ($key, $value, $strict)
{
    return $value['id'] == 3 || $value['title'] == 'Macbeth';
});
```
