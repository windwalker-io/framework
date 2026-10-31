---
applyTo: "**/{test,Test,tests,Tests}/**/*.php"
---

# Testing Instructions

For the full workflow of adding, updating, and running tests, see the
[test skill](../skills/test/SKILL.md).

## Account for local PHPUnit scope settings

The root `phpunit.xml` is the primary test configuration, but a user's local settings may disable test scopes such as
database, service, or package suites. If the requested scope is disabled, do not treat tests that were not run as
passing: use a temporary configuration based on `phpunit.xml.dist` and remove it when finished, or supply the needed
settings through PHPUnit CLI options where available. Preserve any pre-existing local `phpunit.xml`.

## Prefer attributes for lifecycle hooks

Prefer `#[Before]` / `#[After]`, `#[BeforeClass]` / `#[AfterClass]`, and `#[PreCondition]` / `#[PostCondition]`
over conventional hooks (`setUp()`, `tearDown()`, etc.). Import from `PHPUnit\Framework\Attributes`; use descriptive
method names, `void` returns, and static methods for class-level hooks. Avoid empty hooks and preserve inherited
parent/trait behavior and execution order when migrating.

## Use coverage to find missing tests

When the user asks to add missing tests, use code coverage when a coverage driver is available to identify
uncovered lines and branches in the requested scope. Add meaningful behavior assertions for those gaps, including
edge cases and error paths, then rerun the same coverage scope to check the improvement. Do not chase percentages
with assertion-free tests or new coverage exclusions; preserve the logic-free getter/setter exception below.
If coverage cannot run, report the limitation rather than claiming the gaps are covered.

## Keep app-level tooling out of package test scope

Files such as `src/*Package.php`, `etc/*.config.php`, and `src/Command/*` are generally wiring or tools used by a
Windwalker framework application, rather than package behavior that needs standalone tests. Do not add tests solely
for these files or include them in a package's coverage target by default. Test them only when they contain
meaningful, independently testable logic or the requested change specifically requires validating their behavior.

See [Testing and Coverage](../docs/testing.md) for test selectors, driver setup, report commands, and scope caveats.

## Exclude logic-free getters/setters from code coverage and tests

Do not write separate tests solely to exercise pure getters/setters (only return or assign a property, optionally
`return $this`). If such a method has no meaningful coverage through other behavior tests, exclude it from code
coverage with `@codeCoverageIgnore` in the method docblock. If logic is later added (validation, normalization, lazy
init, side effects, etc.), you **must** remove the ignore tag and cover the behavior with tests.

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
