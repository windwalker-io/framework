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
