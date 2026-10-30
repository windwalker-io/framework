---
applyTo: "**/*.php"
---

# Advanced Code Style Instructions

These rules are enforced by `.php-cs-fixer.dist.php` (`@PER-CS` ruleset plus project overrides). Run
`composer cs-check` to verify and `composer cs-fix` to auto-fix. For the full rule set and rationale, see
`/.github/docs/code-style.md`.

## Use New without parentheses in PHP 8.4

Bad

```php
(new FooBar()).run();
```

Good

```php
new FooBar()->run();
```

## Always declare strict types

Every PHP source file must start with `declare(strict_types=1);`.

Bad

```php
<?php

namespace Windwalker\Foo;
```

Good

```php
<?php

declare(strict_types=1);

namespace Windwalker\Foo;
```

## Use `?Type` instead of `Type ... = null` for nullable defaults

Bad

```php
function foo(FooBar $bar = null): void
{
}
```

Good

```php
function foo(?FooBar $bar = null): void
{
}
```

## Sort `use` imports alphabetically and remove unused ones

Bad

```php
use Windwalker\Utilities\Str;
use Windwalker\Data\Collection;
use Windwalker\Unused\Thing;
```

Good

```php
use Windwalker\Data\Collection;
use Windwalker\Utilities\Str;
```

## Use single quotes unless interpolation is needed

Bad

```php
$name = "Simon";
```

Good

```php
$name = 'Simon';
$greet = "Hello, $name";
```

## No trailing comma in multiline arrays/arguments/parameters

Bad

```php
$array = [
    'foo',
    'bar',
];
```

Good

```php
$array = [
    'foo',
    'bar'
];
```

## One space around the concatenation operator

Bad

```php
$str = $a.$b;
```

Good

```php
$str = $a . $b;
```

## Use short array syntax

Bad

```php
$array = array('foo', 'bar');
```

Good

```php
$array = ['foo', 'bar'];
```

## No Yoda conditions

Bad

```php
if (null === $value) {
}
```

Good

```php
if ($value === null) {
}
```

