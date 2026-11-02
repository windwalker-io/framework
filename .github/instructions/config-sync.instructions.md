---
applyTo: "**/composer.json,**/phpunit*.xml*,.github/workflows/*.yml,docker-compose*.yml"
---

# Keep Related Configs and Packages in Sync

Root and per-package configs mirror each other. When changing one, change its counterparts in the same commit.

- **`composer.json`**: update the owning `packages/{name}/composer.json`, and every other package that depends on or
  re-exports the change (e.g. new `ext-*`, keywords, `suggest`). See
  [dependencies.instructions.md](dependencies.instructions.md) for root synchronization.
- **PHPUnit configs**: a new/changed DSN constant, env var, or test group must be applied to the root
  `phpunit.xml.dist`, `phpunit.ci.xml`, `phpunit.windows.xml`, and to `phpunit.xml.dist` / `phpunit.ci.xml` /
  `phpunit.travis.xml` of each package whose tests use it (e.g. `database`, `orm`, `cache`, `session`, `queue`).
  Enable it in `*.ci.xml` only when CI provides the service; keep it commented in `*.dist` / `*.travis.xml`.
- **CI** (`.github/workflows/ci.yml`): services, PHP extensions, and system drivers must match the PHPUnit config
  used by each job. Jobs without a service (e.g. Windows) must use a config that omits that DSN so tests skip.
- **Docker** (`docker-compose*.yml`): keep service credentials aligned with the DSNs in the PHPUnit configs and
  the database docs (`/.github/docs/database-testing.md`).
