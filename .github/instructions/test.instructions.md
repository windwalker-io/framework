---
applyTo: "**/{test,Test,tests,Tests}/**/*.php"
---

# Testing Instructions

## PHPUnit Mock – Invocation Count Matchers

Use `$this->` instead of `self::` for all count matchers.

```php
// Bad
$mock->expects(self::once())->method('foo');

// Good
$mock->expects($this->once())->method('foo');
```

Applies to: `once()`, `never()`, `any()`, `exactly($n)`, `atLeastOnce()`, `atMost($n)`.

