# Code Style

Windwalker Framework uses [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) to enforce a consistent code
style across all packages. The configuration lives in `/.php-cs-fixer.dist.php` at the repository root.

For bite-sized "bad vs. good" examples meant to be followed while writing code, see
`/.github/instructions/cs.instructions.md`. This document explains the *why* and gives the full picture of the
ruleset, for anyone who needs more context than the quick instructions provide.

## Running the fixer

Run from the repository root with development dependencies installed (`composer install`). Use a PHP CLI supported
by the installed fixer and preferably matching the project's minimum PHP target, so formatting does not introduce
syntax unavailable on that target.

For normal changes, restrict the run to the PHP files you touched:

```bash
# Check without modifying files
composer cs-check -- --path-mode=intersection packages/cache/src/CachePool.php packages/cache/test/CachePoolTest.php

# Apply fixes only to those files
composer cs-fix -- --path-mode=intersection packages/cache/src/CachePool.php packages/cache/test/CachePoolTest.php
```

The `--` forwards options to the Composer script. `--path-mode=intersection` keeps the configuration's Finder
exclusions when paths are supplied (the fixer's default `override` mode replaces its Finder paths).
Review the resulting diff and rerun the same check. Checks exit nonzero when formatting changes are needed.
The configuration enables risky rules, so review semantic changes as well as whitespace.

For an intentional full-tree check or formatting pass:

```bash
# Check only (CI-friendly, shows a diff, does not modify files)
composer cs-check

# Auto-fix all files in-place
composer cs-fix
```

Both commands are defined in the root `../../composer.json` and internally run:

```bash
php vendor/bin/php-cs-fixer check --diff
php vendor/bin/php-cs-fixer fix
```

Composer resolves the `php-cs-fixer` binary from `vendor/bin`; the direct equivalents above need no global
installation. You can also append `--path-mode=intersection` and file paths to either direct command.
The fixer automatically discovers the root configuration; add `--config=.php-cs-fixer.dist.php` to explicitly use
the tracked rules instead of a local `.php-cs-fixer.php` override.

The fixer scans every `*.php` file under `../../packages`, excluding `tmp/` and `resources/` directories and `*.blade.php`
templates (see the `Finder` setup in `.php-cs-fixer.dist.php`).

## Baseline ruleset: `@PER-CS`

The project builds on top of the official **PER Coding Style** (`@PER-CS`) ruleset — the successor to PSR-12,
maintained by the PHP-FIG. It covers the general formatting baseline: indentation (4 spaces), brace placement,
blank-line rules, import ordering basics, visibility modifiers, etc. We don't re-document all of `@PER-CS` here;
refer to the [PER Coding Style spec](https://www.php-fig.org/per/coding-style/) and the
[PHP-CS-Fixer ruleset reference](https://cs.symfony.com/doc/ruleSets/PER-CS.html) if you need details beyond what's
listed below.

`setRiskyAllowed(true)` is enabled because some of the project overrides (e.g. `declare_strict_types`,
`global_namespace_import`) are considered "risky" by PHP-CS-Fixer (they can change runtime behavior in edge cases),
but are intentional, reviewed choices for this codebase.

## Project overrides on top of `@PER-CS`

These rules customize or override the `@PER-CS` defaults. Each entry below maps to a key in the `setRules([...])`
array of `../../.php-cs-fixer.dist.php`.

| Rule | Setting | Effect |
| --- | --- | --- |
| `declare_strict_types` | `true` | Every file must declare `strict_types=1`. |
| `new_with_parentheses` | `anonymous_class: false`, `named_class: false` | `new Foo()` without wrapping parentheses is used instead of `(new Foo())`, enabling PHP 8.4's `new Foo()->bar()` chaining syntax. |
| `nullable_type_declaration_for_default_null_value` | `true` | Parameters defaulting to `null` must have an explicit nullable type (`?Foo $foo = null`). |
| `ordered_imports` | `sort_algorithm: alpha` | `use` statements are sorted alphabetically. |
| `no_unused_imports` | `true` | Unused `use` statements are removed. |
| `single_quote` | `true` | Prefer `'...'` over `"..."` when no interpolation/escapes are needed. |
| `trailing_comma_in_multiline` | `false` | No trailing comma after the last element in multiline arrays, argument lists, or parameter lists. |
| `concat_space` | `spacing: one` | Exactly one space around the `.` string concatenation operator. |
| `array_syntax` | `syntax: short` | Use `[...]` instead of `array(...)`. |
| `phpdoc_align` | `align: vertical`, `spacing: 2` | DocBlock tags (`@param`, `@return`, ...) are vertically aligned with 2 spaces of padding. |
| `phpdoc_summary` | `false` | Disabled — DocBlock summary lines are not forced to end with a period or follow a strict format. |
| `phpdoc_to_comment` | `false` | Disabled — DocBlocks that could be plain comments are left as DocBlocks (not downgraded). |
| `yoda_style` | `false` | Disabled — Yoda conditions (`null === $x`) are *not* enforced; normal order (`$x === null`) is preferred, see the instructions file. |
| `increment_style` | `false` | Disabled — no enforced preference between `$i++` and `++$i`. |
| `global_namespace_import` | `import_classes: null`, `import_constants: null`, `import_functions: null` | No automatic addition/removal of `use` statements for global-namespace classes, constants, or functions — left to the author's discretion. |

## Indentation & line endings

- Indent: 4 spaces (`setIndent('    ')`).
- Line endings: `\n` (Unix-style, `setLineEnding("\n")`), even on Windows/macOS.

## Relationship with `../../AGENTS.md`

`../../AGENTS.md` at the repository root also lists broader, non-automatable conventions (no global state, no static
methods except pure utilities, no facades, no magic methods, etc.) that PHP-CS-Fixer cannot enforce mechanically.
Those are a matter of code review, not of the fixer configuration described here.
