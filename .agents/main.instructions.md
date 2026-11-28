# Copilot Instructions

Single entry point for AI agents in this repository. Detailed rules live in `.agents/` (see the links below);
follow them instead of duplicating content here.

## Architecture Overview

**Monorepo of 31 standalone PHP packages** under `packages/`. Each package is independently installable via Composer
but shares one dev environment at the root. The root `composer.json` maps every `Windwalker\{PackageName}\` namespace
and autoloads each package's `src/bootstrap.php` / `src/functions.php`; each package's `composer.json` is also listed
under `"replace"`, so the root acts as all packages at once.

This repository ships the **components** consumed by the full framework (`windwalker/windwalker`). It is not a
runnable application.

**PHP requirement:** ≥ 8.4.6 (see root `composer.json`).

## Package Layout

```
packages/{name}/
  src/              # PSR-4 source (Windwalker\{Name}\)
  src/bootstrap.php # Autoloaded via composer `files`; keep light
  src/functions.php # Namespaced helpers (e.g. \Windwalker\collect(), \Windwalker\raw())
  test/             # PHPUnit tests (Windwalker\{Name}\Test\)
  composer.json     # Standalone package metadata
  phpunit.xml.dist  # Per-package config (prefer the root config, see Testing)
```

Not every package has `bootstrap.php`/`functions.php`.

## General Guidelines

When creating temporary/scratch test files for exploration or debugging (not part of the actual test suite under
`packages/*/test`), put them in the repo-root `tmp/` directory (already git-ignored via `/tmp/*`). Clean them up
when done.

## Code Style

- PER-CS via PHP-CS-Fixer; `declare(strict_types=1);` in every source file.
- Docblocks only for non-obvious code.
- No global state/singletons, facades, service locators, or magic methods (`__call`, `__get`); use DI.
- Static methods only for pure utilities (e.g. `StrInflector`, `Arr`, `Str`).
- No global functions except namespaced helpers in `functions.php`.
- Prefer property hooks / asymmetric visibility (`public protected(set)`) over getter/setter methods.
- No deprecated or legacy PHP patterns.

Details:

- `instructions/cs.instructions.md` - Advanced PHP code style rules.
- `docs/code-style.md` - Full PHP-CS-Fixer ruleset rationale.

## Testing

Run PHPUnit from the repo root with an explicit path, e.g. `php vendor/bin/phpunit packages/orm/test`. Database tests
need MySQL and must not run in parallel; HTTP tests need `php -S localhost:8000 bin/test-server.php`.

Base classes: `AbstractDatabaseTestCase` (`packages/database/src/Test/`), `AbstractORMTestCase` (`packages/orm/test/`).

- `instructions/test.instructions.md` - Testing-specific code style and deprecated/preferred
  APIs.
- [test skill](skills/test/SKILL.md) - Workflow for adding, updating, and running tests.
- `docs/database-testing.md` - Real-database (MySQL) test environment and conventions.
- `docs/testing.md` - Test execution, coverage drivers, reports, and finding missing tests.

## Others

- [Config Sync](instructions/config-sync.instructions.md) - When changing composer / CI / PHPUnit / docker configs,
  update the related packages' counterparts too.
- [Dependency Checks](instructions/dependencies.instructions.md) - Package `require` / `require-dev` declarations
  and root Composer synchronization.
- [Instruction Maintenance](instructions/instruction.instructions.md) - Where to place new rules across
  instructions / skills / docs.

## Docs Reference Map

`.agents/docs/*.md` files are **not** auto-loaded — consult them proactively when a task touches the matching
feature/topic below. Keep this list alphabetical and append a new bullet whenever a new doc is added to
`.agents/docs/` (also update `.agents/docs/README.md`'s table at the same time).

- **Code style / PHP-CS-Fixer rules (`@PER-CS`, rule overrides, rationale)** → `docs/code-style.md`
- **Database tests (MySQL setup, DSN, never run suites in parallel)** → `docs/database-testing.md`
- **Testing / PHPUnit execution and code coverage** → `docs/testing.md`

## Project-Specific Conventions

- Array/int option flags are moving to `***Options` classes (e.g. `ORM::FOR_UPDATE` →
  `new ORMOptions(forUpdate: true)`); old constants remain but are deprecated.
- `StrInflector` (`packages/utilities/src/StrInflector.php`) handles singular/plural, e.g. default `Table` alias.
