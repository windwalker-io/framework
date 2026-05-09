# Windwalker Framework – Agent Guide

## Architecture Overview

**Monorepo of 26+ standalone PHP packages** under `packages/`. Each package is independently installable via Composer
but shares a unified dev environment at the root. The root `composer.json` maps all namespace prefixes (
`Windwalker\{PackageName}\`) and autoloads each package's `src/bootstrap.php`.

This repository ships the **components** consumed by the full Windwalker framework (`windwalker/windwalker`). It is not
a runnable application.

**PHP requirement:** ≥ 8.4.6. Code uses PHP 8.4 features (asymmetric visibility `public protected(set)`, named
arguments, first-class callables).

## Code Style

- PSR-12 coding style.
- Strict types (`declare(strict_types=1);` in all source files).
- Docblocks only for non-obvious code; no redundant `@param`/`@return` when types are clear from signatures.
- No global state or singletons; dependencies injected via constructors or method parameters.
- No static methods except for pure utility functions (e.g. `StrInflector`, `Arr` or `Str`).
- No facades or service locators; use explicit class references and dependency injection.
- No global functions except for namespaced helpers defined in `functions.php` (e.g. `\Windwalker\collect()`,
  `\Windwalker\raw()`). These are autoloaded via Composer's `files` autoloading and are not procedural global functions.
- No magic methods (`__call`, `__get`, etc.) – all behavior should be explicit and discoverable via class methods and
  properties.
- No deprecated features or legacy patterns; the codebase is modern PHP 8.4+ and should not contain any legacy PHP 5/7
  constructs.
- Use PHP 8.5 property hooks with asymmetric visibility (`public protected(set)`) for properties prior than legacy
  getter/setter methods. This allows read-only public access while still allowing internal mutation when necessary.

All code style instructions see: `/.github/instructions/cs.instructions.md`.

All test code style instructions see: `/.github/instructions/test.instructions.md`.

## Package Layout

Every package follows this structure:

```
packages/{name}/
  src/              # PSR-4 source (Windwalker\{Name}\)
  src/bootstrap.php # Loaded by Composer autoload files – registers closures/helpers
  src/functions.php # Global helper functions (e.g. \Windwalker\collect(), \Windwalker\raw())
  test/             # PHPUnit tests (Windwalker\{Name}\Test\)
  composer.json     # Standalone package metadata
  phpunit.xml.dist  # Per-package PHPUnit config
```

All source files begin with `declare(strict_types=1);`.

## Developer Workflows

### Running Tests

```bash
# All enabled tests (edit phpunit.xml to uncomment suites)
php vendor/bin/phpunit

# Single package suite
php vendor/bin/phpunit --configuration packages/orm/phpunit.xml.dist

# Database/HTTP tests require setup first:
php -S localhost:8000 bin/test-server.php   # HTTP test server
# Set WINDWALKER_TEST_DB_DSN_MYSQL in phpunit.xml
```

The root `phpunit.xml` is copied from `phpunit.xml.dist` and can be edited to enable/disable test suites. Each package
also has its own `phpunit.xml.dist` for package-specific configuration (e.g. database DSN).

### Test Base Classes

- DB tests extend `AbstractDatabaseTestCase` (in `packages/database/src/Test/`).
- ORM tests extend `AbstractORMTestCase` which wires `ORM` to a live DB adapter.
- Executed SQL is logged to `packages/database/tmp/test-sql.sql` during test runs.

## Project-Specific Conventions

- **Global helpers** are namespaced functions, not procedural: e.g. `\Windwalker\collect([...])`,
  `\Windwalker\raw($sql)`. Defined in `functions.php`, auto-loaded.
- **Bootstrap files** (`src/bootstrap.php`) use autoload `files` – do not put heavy logic there; they mostly declare
  global function aliases.
- **Deprecated options key** for some array or int options has moved to `***Options` classes (e.g. `ORM::FOR_UPDATE` →
  `new ORMOptions(forUpdate: true)`) – the old constants still exist but are marked deprecated.
- **StrInflector** (`packages/utilities/src/StrInflector.php`) is used project-wide for singular/plural table alias
  resolution (e.g. `Table` attribute alias defaults to `StrInflector::toSingular($tableName)`).
- Each package's `composer.json` is also listed under `"replace"` in the root – this lets Composer treat the monorepo
  root as all packages simultaneously.
