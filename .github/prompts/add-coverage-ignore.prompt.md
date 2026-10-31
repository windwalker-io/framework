---
---

Inspect the specified package or selected scope in this Windwalker Framework monorepo, and mark code that does not
need tests with coverage ignore annotations. Also mark temporary integration/coverage-only test classes so they can be
removed easily later.

1. Identify the target package from the user's request, selected files, or tagged scope. Keep changes within that scope.
2. Read `.github/instructions/test.instructions.md`, `.github/instructions/cs.instructions.md`, and
   `.github/docs/testing.md` first, and follow their rules.
3. Find source code that has no need for tests and add `@codeCoverageIgnore` in its docblock:
   - Logic-free getters/setters (only return/assign a property, optionally `return $this`) without meaningful coverage
     from other behavior tests.
   - Wiring/tooling files that the instructions say need no standalone tests (e.g. `src/*Package.php`,
     `etc/*.config.php`, `src/Command/*` or some DI service providers), unless they contain meaningful, independently testable logic.
   - Do not ignore code with real logic (validation, normalization, lazy init, side effects, branching). Cover it with
     tests instead of ignoring it.
4. Find test classes that exist only for integration testing or coverage purposes (e.g. classes added just to exercise
   otherwise-unreached code). Add a class-level docblock or comment to each, stating its purpose, e.g.:

   ```php
   /**
    * Integration/coverage-only test. Safe to remove when it is no longer needed.
    */
   ```

   This lets them be found and deleted in the future. Do not annotate ordinary unit tests.
5. Do not change production behavior. Preserve unrelated working-tree changes.
6. Run the relevant style check and the affected package tests, then inspect the final diff.

Large-scope handling:

- If the scope is large (many files or many subdirectories, e.g. a whole package or several packages), do not process
  it all in one pass. Split it into multiple iterations, one subdirectory (or small batch of files) per iteration, and
  finish each one (inspect, change, verify) before starting the next.
- Keep a short checklist of the subdirectories/files and mark each as done, so most files are genuinely inspected
  rather than skimmed, and context stays small enough to keep focus.
- Read only the files for the current iteration; avoid carrying earlier iterations' file contents forward.
- Run the targeted check/tests per iteration, then do the final overall verification and summary at the end.

Success means every ignored item is justified by the instructions, no logic-bearing code is ignored, all
integration/coverage-only test classes carry a clear removal-friendly docblock, and tests and style checks pass.
Summarize the changed files and what was ignored or annotated.
