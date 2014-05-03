# Windwalker Compare Package

## Installation via Composer

Add `"ventoviro/windwalker-compare": "1.0.*"` to the require block in your composer.json.

``` json
{
    "require": {
        "ventoviro/windwalker-compare": "1.0.*"
    }
}
```

## What is Compare

Sometimes we will need a dynamic compare interface, but it hard to convert `=` or `<=` string to be php operator.

Compare object can help us create an object with compare logic between two values, and convert it to string, then we can use this string to build SQL or other use.

## Basic Usage

``` php
echo new GteCompare('published', '1');
```

We will get `published >= 1` string. This is easy to integate into query string.

``` php
$conditions = array(
    GteCompare('published', '1'),
    EqCompare('entry_id', 25),
    LteCompare('date', $query->quote($date))
);

$sql = 'WHERE ' . implode(' AND ' , $conditions);
```

We will get `WHERE published >= 1 AND entry_id = 25 AND data <= '2014-03-02'`.

## Do Compare

``` php
$compare = new GteCompare(3, '1');

$result = $compare->compare();

var_dump($result); // bool(true)
```

## Available Compare Object

| Name       | Description      | Operator |
| ---------- | ---------------- | -------- |
| EqCompare  | Equal                | `=`  |
| NeqCompare | Not Equal            | `!=` |
| GtCompare  | Greater than         | `>`  |
| GteCompare | Greate than or Equal | `>=` |
| LtCompare  | Less than            | `<`  |
| LteCompare | Less than or Equal   | `<=` |


