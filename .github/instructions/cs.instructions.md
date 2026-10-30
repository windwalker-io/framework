---
applyTo: "**/*.php"
---

# Advanced Code Style Instructions

These rules are enforced by `.php-cs-fixer.dist.php` (`@PER-CS` ruleset plus project overrides). Run
`composer cs-check` to verify and `composer cs-fix` to auto-fix. For the full rule set and rationale, see
`/.github/docs/code-style.md`.

## Running PHP-CS-Fixer

Run from the repository root. Prefer limiting checks/fixes to the changed PHP files so unrelated work is untouched:

```bash
composer cs-check -- --path-mode=intersection packages/cache/src/CachePool.php
composer cs-fix -- --path-mode=intersection packages/cache/src/CachePool.php
```

Pass multiple file paths when needed. `--path-mode=intersection` preserves the configured Finder exclusions.
Run the check again after fixing and review the diff. Bare `composer cs-check` / `composer cs-fix` scan the whole
configured tree; use them only when that scope is intended. See the [fixer guide](../docs/code-style.md#running-the-fixer)
for prerequisites and direct CLI equivalents.

## Clean up phpdoc

Some phpdoc with a simple description which same as function name, and a `@since __DEPLOY_VERSION__` tag,
is legacy from the old codebase, and should be removed. Keep necessary tags, and remove the rest.

```php
/**
     * withRemoveDeep
     *
     * ...
     *
     * @since  __DEPLOY_VERSION__
     */
    public function withRemoveDeep()
```

## Use New without parentheses in PHP 8.4

Bad

```php
(new FooBar())->run();
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
