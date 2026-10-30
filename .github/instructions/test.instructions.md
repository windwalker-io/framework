---
applyTo: "**/{test,Test,tests,Tests}/**/*.php"
---

# Testing Instructions

For the full workflow of adding, updating, and running tests, see the
[test skill](../skills/test/SKILL.md).

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

## Deprecated / Preferred APIs (Quick Rules)

Add new "use / don't use" rules here as bullets so they stay easy to scan.

### Exception Message Matchers

Don't use `expectExceptionMessage()` (deprecated in PHPUnit 13).

- Use `expectExceptionMessageIsOrContains()` for exact-or-substring match.
- Use `expectExceptionMessageIs()` for an exact match.
- Use `expectExceptionMessageMatches()` for a regex match.

### PHPUnit Mock – Invocation Count Matchers

Don't use `self::` for PHPUnit mock invocation count matchers; use `$this->` (applies to `once()`, `never()`,
  `any()`, `exactly($n)`, `atLeastOnce()`, `atMost($n)`).

```php
// Bad
$mock->expects(self::once())->method('foo');

// Good
$mock->expects($this->once())->method('foo');
```
