# Windwalker String

Windwalker String package provides UTF-8 string operation, it is a Joomla String fork but added more functions.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/string": "~3.0"
    }
}
```

## New Str Class

After 3.2, Windwalker has a new `Str` class and we will continue replace all old `StringHelper` will it in the future:

```php
use Windwalker\String\Str;

string : Str::getChar($string, $pos, $encoding = null);
string : Str::between($string, $start, $end, $offset = 0, $encoding = null);
string : Str::collapseWhitespaces($string);
bool :   Str::contains($string, $search, $caseSensitive = true, $encoding = null);
bool :   Str::endsWith($string, $search, $caseSensitive = true, $encoding = null);
bool :   Str::startsWith($string, $target, $caseSensitive = true, $encoding = null);
string : Str::ensureLeft($string, $search, $encoding = null);
string : Str::ensureRight($string, $search, $encoding = null);
bool :   Str::hasLowerCase($string, $encoding = null);
bool :   Str::hasUpperCase($string, $encoding = null);
bool :   Str::match($pattern, $string, $option = 'msr', $encoding = null);
string : Str::insert($string, $insert, $position, $encoding = null);
bool :   Str::isLowerCase($string);
bool :   Str::isUpperCase($string);
string : Str::first($string, $length = 1, $encoding = null);
string : Str::last($string, $length = 1, $encoding = null);
string : Str::intersectLeft($string1, $string2, $encoding = null);
string : Str::intersectRight($string1, $string2, $encoding = null);
string : Str::intersect($string1, $string2, $encoding = null);
string : Str::pad($string, $length = 0, $substring = ' ', $encoding = null);;
string : Str::padLeft($string, $length = 0, $substring = ' ', $encoding = null);;
string : Str::padRight($string, $length = 0, $substring = ' ', $encoding = null);;
string : Str::removeChar($string, $offset, $length = null, $encoding = null);
string : Str::removeLeft($string, $search, $encoding = null);
string : Str::removeRight($string, $search, $encoding = null);
string : Str::slice($string, $start, $end = null, $encoding = null);
string : Str::substring($string, $start, $end = null, $encoding = null);
string : Str::surround($string, $substring = ['"', '"']);
string : Str::toggleCase($string, $encoding = null);
string : Str::truncate();;
string : Str::map($string, callable $callback, $encoding = null);
string : Str::filter($string, callable $callback, $encoding = null);
string : Str::reject($string, callable $callback, $encoding = null);
```

## New StringObject

`StringObject` is a class to help us manipulation string by OO way.

Create string object:

```php
$str = str('Hello');

// OR

$str = new StringObject('Hello');
$str = StringObject::create('Hello');
```

### Usage

```php
$str[3]; // Get letter

// Iterator
foreach ($str as $letter)
{
    echo $letter
}

// Chaining modify, it is a immutable object, must reuturn self.
$str = $str->toUpperCase()
    ->trimLeft()
    ->truncate();
    
// to string
echo $str;
```

### Methods

```php
$str->count();
$str->getString();
$str->withString($string);
$str->toLowerCase();
$str->toUpperCase();
$str->length();
$str->chop($length = 1);
$str->replace($search, $replacement, &$count = null);
$str->compare($compare, $caseSensitive = true);
$str->reverse();
$str->substrReplace($replace, $start, $offset = null);
$str->trimLeft($charlist = null);
$str->trimRight($charlist = null);
$str->trim($charlist = null);
$str->upperCaseFirst();
$str->lowerCaseFirst();
$str->upperCaseWords();
$str->substrCount($search, $caseSensitive = true);
$str->indexOf($search);
$str->indexOfLast($search);
$str->explode($delimiter, $limit = null);
$str->apply(callable $callback);
$str->getChar(int $pos);
$str->between(string $start, string $end, int $offset = 0);
$str->collapseWhitespaces(string $string);
$str->contains(string $search, bool $caseSensitive = true);
$str->endsWith(string $search, bool $caseSensitive = true);
$str->startsWith(string $target, bool $caseSensitive = true);
$str->ensureLeft(string $search);
$str->ensureRight(string $search);
$str->hasLowerCase();
$str->hasUpperCase();
$str->match(string $pattern, string $option = 'msr');
$str->insert(string $insert, int $position);
$str->isLowerCase();
$str->isUpperCase();
$str->first(int $length = 1);
$str->last(int $length = 1);
$str->intersectLeft(string $string2);
$str->intersectRight(string $string2);
$str->intersect(string $string2);
$str->pad(int $length = 0, string $substring = ' ');
$str->padLeft(int $length = 0, string $substring = ' ');
$str->padRight(int $length = 0, string $substring = ' ');
$str->removeChar(int $offset, int $length = null);
$str->removeLeft(string $search);
$str->removeRight(string $search);
$str->slice(int $start, int $end = null);
$str->substring(int $start, int $end = null);
$str->surround($substring = ['"', '"']);
$str->toggleCase();
$str->truncate(int $length, string $suffix = '', bool $wordBreak = true);
$str->map(callable $callback);
$str->filter(callable $callback);
$str->reject(callable $callback);
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
