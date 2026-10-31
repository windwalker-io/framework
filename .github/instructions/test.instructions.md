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

## Document test fixtures and integration targets

Add a class docblock to test-only fixtures and integration targets explaining their role and the behavior or tests that
depend on them. State when the fixture can be removed (for example, once the covered behavior is removed and no tests
reference it), so future maintainers can distinguish intentional test support from unused code.

## Keep app-level tooling out of package test scope

Files such as `src/*Package.php`, `etc/*.config.php` and `src/Command/*` or some DI service providers are generally wiring or tools used by a
Windwalker framework application, rather than package behavior that needs standalone tests. Do not add tests solely
for these files or include them in a package's coverage target by default. Test them only when they contain
meaningful, independently testable logic or the requested change specifically requires validating their behavior.

Tests touching a real database: see [Database Testing](../docs/database-testing.md) (never run DB suites in parallel).

See [Testing and Coverage](../docs/testing.md) for test selectors, driver setup, report commands, and scope caveats.

## Filesystem tests: cross-platform paths and virtual storage

- When testing the real filesystem, use
  [`FilesystemTestTrait`](../../packages/filesystem/test/FilesystemTestTrait.php)
  (`Windwalker\Filesystem\Test\FilesystemTestTrait`) for path comparisons across Windows and Unix-like systems.
  Use `assertPathEquals()` (`Path::clean()`) or `assertRealpathEquals()` (`Path::normalize()`) as appropriate;
  do not compare raw path strings when separator differences are irrelevant to the behavior under test.
  Despite its name, `assertRealpathEquals()` normalizes paths; it does not resolve them with PHP's `realpath()`.
- For file storage/read/write tests that do not require real filesystem or OS-specific behavior, VFS
  (`org\bovigo\vfs\vfsStream`, `vfs://` paths) can provide isolated virtual fixtures instead of writing to disk.
  Reuse [`AbstractVfsTestCase`](../../packages/filesystem/test/AbstractVfsTestCase.php) when its fixture setup fits.
  Use real temporary files when the API does not support stream wrappers or the test requires native filesystem
  semantics, and clean them up after the test.

## Exclude logic-free getters/setters or simple proxy methods from code coverage and tests

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

Also exclude simple proxy methods that only call another method or service without adding meaningful behavior.
But if the parameters or return value are transformed, validated, or otherwise processed, cover the behavior with tests.

```php
/**
 * @codeCoverageIgnore
 */
public function createAnyInstance(array $options): static
{
    return $this->builder->createAnyInstance($options);
}
```

## Never ignore branches with logic or thrown errors

`@codeCoverageIgnore` is only for logic-free getters/setters and simple proxy methods. Any branch that contains logic
(conditions, transformation, fallback, side effects) or throws an exception **must** be tested, even if it is only a
few lines. Do not ignore such branches to raise coverage. Only code that truly cannot be exercised (e.g. an
unavailable external driver or process-level `exit`/signal handling) may be ignored, with the reason stated in the tag.

A branch that is purely defensive or a fallback and is proven unreachable through the public behavior (e.g. a caller
already guarantees the condition) may also be ignored. Wrap it with `@codeCoverageIgnoreStart` / `End` (or tag the
method) and explain in a comment why it cannot be reached. Verify the claim first; if it is reachable, test it.

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

### Test Doubles (Mock / Stub)

When the subject calls many other objects or services, isolate it with test doubles instead of building real data or
wiring real dependencies for every test. Use real fixtures/DB only when the behavior under test is the integration
itself.

Use the PHPUnit 13 APIs:

- `$this->createStub(Foo::class)` for a double that only supplies return values (no expectations). Configure with
  `->method('x')->willReturn(...)`. Do not call `expects()` on a stub.
- `$this->createMock(Foo::class)` only when the test verifies interactions via `expects($this->once())` etc. A mock
  with no `expects()` is reported as a notice; use `createStub()` instead.
- `createStubForIntersectionOfInterfaces()` / `createMockForIntersectionOfInterfaces()` for multi-interface doubles.
- Use `willReturnCallback()`, `willReturnOnConsecutiveCalls()` or `willReturnMap()`; `withConsecutive()` no longer exists.
- Don't use `setMethods()`, `addMethods()` or `onlyMethods()`; mock an interface or extract a collaborator instead.
- Prefer anonymous classes or small named test stubs for plain value objects.

```php
// Good: only needs a return value
$repo = $this->createStub(UserRepository::class);
$repo->method('find')->willReturn($user);

// Good: verifies the interaction
$logger = $this->createMock(LoggerInterface::class);
$logger->expects($this->once())->method('error');
```
