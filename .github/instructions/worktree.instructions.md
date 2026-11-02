---
applyTo: "**"
---

# Worktree Mode

These rules apply only when the task runs in a git worktree (worktree isolation mode).

- **Naming**: when starting a new task in worktree mode, ask the user whether they want a custom worktree/branch
  name before creating it. Fall back to a name derived from the task if they have no preference.
- **Commit every change**: commit directly inside the worktree after each change; do not leave uncommitted work.
- **Merging**: when the user asks to merge the worktree into the current working branch, squash all worktree
  commits into a single commit and write the commit message yourself from the actual changes (no need to ask).
- **Continuing**: if the user gives further instructions after a merge, keep working in the same worktree
  (keep committing each change) and squash-merge again only when asked.
- **Database tests**: when a task needs database tests, first check whether the working branch's environment has a
  database running. If it does, the local or Docker database is available, so use it directly (see
  `/.github/docs/database-testing.md`).
- **Vendor for tests**: a new worktree has no `vendor/`. After writing code, if tests need to run, first symlink the
  main repo's `vendor` into the worktree (`ln -s <main-repo>/vendor vendor`); if the link causes path problems
  that cannot be resolved effectively (or the main repo has no `vendor`), remove the link and run
  `composer install` instead. Do not commit `vendor/`.
- **Keep the link until the end**: the `.githooks/pre-commit` hook runs `vendor/bin/php-cs-fixer` from the worktree
  root and silently skips formatting when it is missing. So create the `vendor` link before the first commit
  (even if no tests are run) and keep it for the whole session; only remove it when the whole task, including
  every commit and merge, is finished. If the main repo's `vendor/bin/php-cs-fixer` is missing, run
  `composer install` in the main repo first. Never stage `vendor/` (check `git status` before committing).
