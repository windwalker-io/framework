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

## Exclude logic-free getters/setters from code coverage

Pure getters/setters (only return or assign a property, optionally `return $this`) may be excluded from code coverage
with `@codeCoverageIgnore` in the method docblock. If logic is later added (validation, normalization, lazy init,
side effects, etc.), you **must** remove the ignore tag so the method is covered by tests again.

```php
/**
 * @codeCoverageIgnore
 */
public function setOptions(array $options): static
{
    $this->options = $options;

    return $this;
}
```
