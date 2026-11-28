---
---

Commit all pending changes in the current git worktree, then merge the worktree into the working branch as a single
squashed commit.

1. In the worktree, commit any uncommitted changes first so nothing is left behind.
2. Identify the working branch the worktree should merge into (the branch of the main checkout). Ask the user only
   if it cannot be determined.
3. Make sure the working branch has no uncommitted changes that would conflict with the merge.
4. Before merge, ask user if they want to provide a custom commit message, append it to the generated commit message from diff.
5. Squash-merge the worktree branch into the working branch (`git merge --squash <worktree-branch>`), then create
   one commit.
6. Write the commit message yourself from the actual diff: a concise imperative subject line, plus a short body
   only when the change needs explanation. Do not ask the user for the message.
7. If the squash merge conflicts, resolve it when the resolution is obvious; otherwise stop and report the
   conflicting files.
8. Keep the worktree and its branch so work can continue; do not delete them unless the user asks.

Success criteria: the working branch gains exactly one new commit containing all worktree changes, the worktree has
no uncommitted changes, and the final commit message accurately describes the change.
