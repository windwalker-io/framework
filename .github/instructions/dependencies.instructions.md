---
applyTo: "**/*.php,**/composer.json"
---

# Dependency Checks

- When writing or modifying production code or tests that reference a class outside the owning package,
  identify the Composer package providing that class and check `packages/{name}/composer.json` for a suitable
  dependency declaration. This includes other `windwalker/*` packages and third-party libraries.
  Availability through the root autoloader or root dependencies is not sufficient for a standalone package.
- Declare runtime dependencies in the owning package's `require`; declare dependencies used only for tests or
  development in `require-dev`. If already covered by a suitable `require` entry, do not duplicate it in
  `require-dev`. PHP built-in classes do not require a Composer package; check `ext-*` requirements when applicable.
- Before introducing a dependency on an external (non-workspace) package, ask the user and obtain approval
  before adding its dependency declaration or installing it. This also applies when the package is already
  available through the root autoloader but is a new dependency for the owning package.
- Run `php bin/merge-composer.php` from the repository root only when a new external package dependency is
  introduced, after user approval and declaration in the owning package's manifest. Review the resulting root
  `composer.json` diff and any version warnings; do not declare package dependencies only in the root manifest.
- Internal `windwalker/*` dependencies must still be declared in the owning package, but do not require running
  `merge-composer-dev.php`: workspace packages are already included through root `replace`, and the script skips
  them in root `require` / `require-dev`.

