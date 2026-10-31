---
name: test
description: Add, update, or run Windwalker Framework PHPUnit tests for any package under packages/*/test. Use for regression tests, trait-based fixtures (DatabaseTestTrait, QueryTestTrait, DOMTestTrait), and targeted test execution within this monorepo.
---

# Windwalker Framework Tests

## Workflow

1. Inspect the changed behavior and neighboring tests under `packages/{name}/test`; read
   [Test Rules](../../instructions/test.instructions.md) (lifecycle attributes, exception and mock matcher style) and
   [Code Style Rules](../../instructions/cs.instructions.md).
2. Match the package's existing test conventions: tests live in `packages/{name}/test/` and are namespaced
   `Windwalker\{Name}\Test\` (PSR-4, see root `composer.json` `autoload-dev`). Mirror the source path structure under
   `src/`.
3. Choose the smallest test boundary. Prefer plain unit tests with mocks/stubs for pure logic. When the subject depends on many
   other objects/services, use PHPUnit 13 stubs/mocks instead of building real data each time (see
   [Test Doubles](../../instructions/test.instructions.md#test-doubles-mock--stub)). For DB/Query/DOM-backed
   behavior, use the traits in `Windwalker\Test\Traits` (e.g. `DatabaseTestTrait`, `QueryTestTrait`, `DOMTestTrait`)
   instead of hand-rolled fixtures.
4. For database tests, confirm a test DB DSN constant (e.g. `WINDWALKER_TEST_DB_DSN_SQLITE`,
   `WINDWALKER_TEST_DB_DSN_MYSQL`) is configured in the root `phpunit.xml` (copied from `phpunit.xml.dist`) or the
   package's own `phpunit.xml.dist`. SQLite (`tmp/test.db`) requires no external service; MySQL/PostgreSQL/Oracle/SQL
   Server require `docker compose up -d` or a local instance first. Never assume a DSN is already uncommented—check
   before relying on it, and stop to ask if uncertain.
5. Write PHPUnit attribute-based tests (`#[Test]`, `#[DataProvider]`, etc. per PHPUnit 13) with
   `declare(strict_types=1);` at the top of every test file, matching the project's PHP 8.4+ style.
   - Test methods do not need a `test` prefix; the `#[Test]` attribute marks them. Name them after the behavior
     (e.g. `resolveCallArgumentsWithVariadic`).
   - Data provider methods must end with `Provider` (e.g. `argumentsProvider`) and be referenced via
     `#[DataProvider('argumentsProvider')]`.
   - Prefer attribute-based lifecycle hooks as defined in the [Test Rules](../../instructions/test.instructions.md);
     preserve inherited fixture behavior when migrating conventional hooks.
6. Run the smallest selector from the repository root:

   ```bash
   # Single test file, optionally narrowed to a method
   php vendor/bin/phpunit packages/{name}/test/ExampleTest.php --filter methodName

   # One package's full suite, using the root configuration and autoloader
   php vendor/bin/phpunit packages/{name}/test

   # All suites enabled by the root configuration
   php vendor/bin/phpunit
   ```

   PHPUnit loads root `phpunit.xml`, falling back to `phpunit.xml.dist`. Do not assume a package's standalone
   configuration works in the monorepo: check its bootstrap path and PHPUnit compatibility first.
   HTTP-related tests may need the bundled test server first: `php -S localhost:8000 bin/test-server.php`.
7. Inspect failures, fix the root cause or fixture, and rerun the selector. Do not claim success if a required
   database or external service was not actually configured/running.
8. When asked to add missing tests, collect baseline coverage for the requested source scope, inspect uncovered
   lines/branches, and cover meaningful missing behavior, edge cases, and error paths. Rerun the same selector and
   source scope after adding tests; inspect the target files rather than relying only on an aggregate percentage.
   Follow the logic-free getter/setter exclusion rule in [Test Rules](../../instructions/test.instructions.md).
   See [Testing and Coverage](../../docs/testing.md) for executable commands, coverage drivers, and configuration
   caveats. If the driver is unavailable or tests are skipped, state the limitation explicitly.
