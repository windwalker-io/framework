# Windwalker Framework – Agent Guide

Short entry point for AI agents. Detailed rules live in `/.github/` (see `/.github/README.md` for how it is organized);
follow the links below instead of duplicating content here.

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

## Code Style (summary)

- PER-CS via PHP-CS-Fixer; `declare(strict_types=1);` in every source file.
- Docblocks only for non-obvious code.
- No global state/singletons, facades, service locators, or magic methods (`__call`, `__get`); use DI.
- Static methods only for pure utilities (e.g. `StrInflector`, `Arr`, `Str`).
- No global functions except namespaced helpers in `functions.php`.
- Prefer property hooks / asymmetric visibility (`public protected(set)`) over getter/setter methods.
- No deprecated or legacy PHP patterns.

Details: `/.github/instructions/cs.instructions.md` (rules), `/.github/docs/code-style.md` (full ruleset, running the
fixer).

## Testing

Run PHPUnit from the repo root with an explicit path, e.g. `php vendor/bin/phpunit packages/orm/test`. Database tests
need MySQL and must not run in parallel; HTTP tests need `php -S localhost:8000 bin/test-server.php`.

- `/.github/skills/test/SKILL.md` – workflow for adding/updating/running tests
- `/.github/instructions/test.instructions.md` – test code style, preferred/deprecated APIs
- `/.github/docs/testing.md` – execution, coverage drivers, reports, finding missing tests
- `/.github/docs/database-testing.md` – MySQL/DSN setup and DB test conventions

Base classes: `AbstractDatabaseTestCase` (`packages/database/src/Test/`), `AbstractORMTestCase`
(`packages/orm/test/`).

## Project-Specific Conventions

- Array/int option flags are moving to `***Options` classes (e.g. `ORM::FOR_UPDATE` →
  `new ORMOptions(forUpdate: true)`); old constants remain but are deprecated.
- `StrInflector` (`packages/utilities/src/StrInflector.php`) handles singular/plural, e.g. default `Table` alias.

## Maintaining AI Docs

Where to put new rules: `/.github/README.md` and `/.github/instructions/instruction.instructions.md`.
