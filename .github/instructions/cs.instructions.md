# Advanced Code Style Instructions

## Use New without parentheses in PHP 8.4 

Bad

```php
(new FooBar()).run();
```

Good

```php
new FooBar()->run();
```
