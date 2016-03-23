# Windwalker String

Windwalker String package provides UTF-8 string operation, it is a Joomla String fork but added more functions.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/string": "~2.0"
    }
}
```

## Simple Template Engine

Simple variable replace.

``` php
use Windwalker\String\SimpleTemplate;

$string = 'Hello my name is: {{ name }}~~~!!!';

SimpleTemplate::render($string, array('name' => 'Simon')); // Hello my name is Simon~~~!!!
```

Multi-level variable.

``` php
$string = 'Hello my name is: {{ user.name }} and ID is: {{ user.id }}~~~!!!';

$array = array(
    'user' => array(
        'name' => 'Simon',
        'id' => 123
    )
);

SimpleTemplate::render($string, $array); // Hello my name is Simon and ID is: 123~~~!!!
```

Custom Tags:


``` php
$string = 'Hello my name is: {$ name $}~~~!!!';

SimpleTemplate::render($string, array('name' => 'Simon'), array('{$', '$}'); // Hello my name is Simon~~~!!!
```

## Utf8String

Utf8String is a wrap of `phputf8` library:

``` php
use Windwalker\String\Utf8String;

$string = '這是一個最美的小情歌';

Utf8String::substr($string, 0, 5); // '這是一個最'
Utf8String::strlen($string); // 10

// More methods
Utf8String::is_ascii($string);

Utf8String::strpos($str, $search, $offset = false);

Utf8String::strrpos($str, $search, $offset = 0);

Utf8String::strtolower($string);

Utf8String::strtoupper($string);

Utf8String::str_ireplace($search, $replace, $str, $count = null);

Utf8String::str_split($str, $split_len = 1);

Utf8String::strcasecmp($str1, $str2, $locale = false);

Utf8String::strcmp($str1, $str2, $locale = false);

Utf8String::strcspn($str, $mask, $start = null, $length = null);

Utf8String::stristr($str, $search);

Utf8String::strrev($string);

Utf8String::strspn($str, $mask, $start = null, $length = null);

Utf8String::substr_replace($str, $repl, $start, $length = null);

Utf8String::ltrim($str, $charlist = null);

Utf8String::rtrim($str, $charlist = null);

Utf8String::trim($str, $charlist = null);

Utf8String::ucfirst($str, $delimiter = null, $newDelimiter = null);

Utf8String::ucwords($string);

Utf8String::transcode($source, $from_encoding, $to_encoding); // Equals to iconv())

Utf8String::valid($string);

Utf8String::compliant($string);

Utf8String::unicode_to_utf8($string);

Utf8String::unicode_to_utf16($string);
```

## StringHelper

### Empty String Determine

isEmpty()

``` php
use Windwalker\String\StringHelper;

StringHelper::isEmpty('');      // true
StringHelper::isEmpty(0);       // false
StringHelper::isEmpty(array()); // true
StringHelper::isEmpty(null);    // true
```

isZero()

``` php
StringHelper::isZero(0);    // true
StringHelper::isZero('0');  // true
StringHelper::isZero('');   // false
StringHelper::isZero(null); // false
```

### Quote

An useful method to quate a string.

``` php
// Default quote is `"`
StringHelper::quote('foo'); // "foo"

// Custom quotes
StringHelper::quote('foo', array('{{', '}}')); // {{foo}}

// Backquote
StringHelper::backquote('foo'); // `foo`
```

### More Methods

increment()

``` php
StringHelper::increment('Title'); // Title (2)
StringHelper::increment('Title', StringHelper::INCREMENT_STYLE_DASH); // Title-2
```

at()

``` php
StringHelper::at('歡迎光臨', 2); // 光
```

collapseWhitespace()

``` php
StringHelper::collapseWhitespace('Welcome   to   Windwalker'); // 'Welcome to Windwalker'
```

endsWith()

``` php
StringHelper::endsWith('歡迎光臨', '光臨' [, $caseSensive = true]); // true
```

startsWith()

``` php
StringHelper::startsWith('歡迎光臨', '歡迎' [, $caseSensive = true]); // true
```

``` php
// Default callback is array_push
StringHelper::explode('.', 'foo.bar', 3); // array('foo', 'bar', null);

// Shift null
StringHelper::explode('.', 'foo.bar', 3, 'array_shift'); // array(null, 'foo', 'bar');

// Limit
StringHelper::explode('.', 'foo.bar.yoo', 2); // array('foo', 'bar.yoo');

// Useful to use list()

list($foo, $bar, $yoo) = StringHelper::explode('.', 'foo.bar', 3);
```

## StringInflector

``` php
use Windwalker\String\StringInflector;

$inflector = StringInflector::getInstance();
$string = 'category';

if ($inflector->isSingular($string))
{
    $string = $inflector->toPular(); // categories
}

if ($inflector->isPlural($string))
{
    $string = $inflector->toSingular(); // category
}
```
