# Testing and Coverage

Run all commands from the repository root, using a PHP CLI compatible with the installed development tools
(check `php -v` and `php vendor/bin/phpunit --version`). The library's minimum PHP version does not necessarily
match PHPUnit's minimum version. Dependencies, including PHPUnit, must be installed via `composer install`.

For test-writing conventions, see [Testing Instructions](../instructions/test.instructions.md); for the
end-to-end workflow, see the [test skill](../skills/test/SKILL.md).

## Running tests

PHPUnit automatically loads root `phpunit.xml`, or `phpunit.xml.dist` if no local configuration exists. The root
configuration bootstraps `vendor/autoload.php`, which maps source and test namespaces for the whole monorepo.
Prefer explicit test paths so the run does not depend on which suites a local configuration enables.

```bash
# One file
php vendor/bin/phpunit packages/cache/test/CachePoolTest.php

# One method in that file
php vendor/bin/phpunit packages/cache/test/CachePoolTest.php --filter methodName

# One package
php vendor/bin/phpunit packages/cache/test

# All suites enabled by the root configuration
php vendor/bin/phpunit
```

Replace `methodName` with an actual test method. To explicitly use the distributed configuration, add
`--configuration phpunit.xml.dist`; use `--configuration phpunit.xml` when local service settings are required.
Some package-level configurations target older PHPUnit versions or expect a package-local `vendor/autoload.php`.
Do not use `--configuration packages/{name}/phpunit.xml.dist` from the monorepo without checking those assumptions.

For service-backed tests, copy `phpunit.xml.dist` to `phpunit.xml` only if the local file does not already exist.
Enable the relevant test DSN constants or service flags there and start the required services. Use test-only
databases: fixtures may create, truncate, or drop tables. SQLite needs its PDO extension and a writable database
directory; external databases need a running local instance or the configured Docker services. HTTP tests may
require `php -S localhost:8000 bin/test-server.php` in a separate terminal.

Skipped tests are not evidence that the corresponding behavior passed. Inspect skips and configuration warnings
before reporting the result.

## Coverage prerequisites

Coverage requires a driver loaded in the **same PHP CLI** running PHPUnit:

```bash
php --ri xdebug
# Or, when using PCOV:
php --ri pcov
```

Use the command for the installed driver; a missing extension is a setup limitation, not zero coverage.
For Xdebug, set `XDEBUG_MODE=coverage` on the PHPUnit process. For PCOV, enable `pcov.enabled=1` and ensure
`pcov.directory` includes the source under test; disable Xdebug when choosing PCOV. PCOV provides line coverage,
while branch/path coverage requires Xdebug and PHPUnit's `--path-coverage` option.

## Generating reports

To keep root configuration constants and bootstrap settings, run the target tests with coverage enabled:

```bash
XDEBUG_MODE=coverage php vendor/bin/phpunit packages/cache/test \
    --coverage-text --coverage-html coverage/cache
```

Read `coverage/cache/index.html` and drill into the target source files to see uncovered executable lines.
Reports belong under the git-ignored root `coverage/` directory, not in committed source.
The distributed root source filter includes all packages, so an aggregate percentage from a single package run
is **not** that package's coverage percentage. `--filter` selects tests, not coverage source files, and
`--coverage-filter` adds source directories rather than replacing an existing XML source filter.

For a source-scoped unit-test report that does not need XML-defined constants, environment variables, or other
settings, bypass XML configuration and provide the autoloader and source filter explicitly:

```bash
XDEBUG_MODE=coverage php vendor/bin/phpunit --no-configuration \
    --bootstrap vendor/autoload.php --coverage-filter packages/cache/src \
    --coverage-text --coverage-html coverage/cache-scoped packages/cache/test
```

With PCOV instead of Xdebug, the equivalent line-coverage run is:

```bash
php -d pcov.enabled=1 -d pcov.directory=packages/cache/src vendor/bin/phpunit \
    --no-configuration --bootstrap vendor/autoload.php --coverage-filter packages/cache/src \
    --coverage-text --coverage-html coverage/cache-scoped packages/cache/test
```

Do not bypass XML configuration for tests that rely on its service settings. Keep the root configuration and
inspect individual target files, or use a separate local configuration preserving the required settings with a
narrowed `<source><include>` filter. Do not change the shared source filter just to improve a reported percentage.

## Filling missing test coverage

When asked to add missing tests, capture a baseline for the requested source scope, inspect uncovered lines and
branches, and write behavior-focused assertions for meaningful gaps, including boundary values and failure paths.
Rerun the same test selection, source filter, and driver after adding tests so the comparison is meaningful.
Use Xdebug with `--path-coverage` when branch/path evidence is needed; line coverage alone does not establish that
every branch was exercised.

Do not inflate coverage with assertion-free calls, unnecessary exclusions, or tests solely for logic-free
getters/setters (see the exception in [Testing Instructions](../instructions/test.instructions.md)).
If a driver or service is unavailable, or relevant tests are skipped, state what could not be verified.
