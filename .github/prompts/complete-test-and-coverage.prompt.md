---
---

Inspect the specified package or relevant selected scope in this Windwalker Framework monorepo for meaningful
missing tests, then add tests to cover the gaps.

1. Identify the target package from the user's request, selected files, or tagged scope. Keep investigation and
   changes within that package unless a directly related shared test helper must be used.
2. Read the package source and neighboring tests, `.github/instructions/test.instructions.md`,
   `.github/instructions/cs.instructions.md`, the `test` skill, and `.github/docs/testing.md`. Follow package test
   conventions and use PHPUnit attributes and the project's existing test traits and fixtures.
3. Run the package's current test suite and capture a package-source-scoped coverage baseline using an available
   coverage driver. Inspect uncovered source lines and branches, then prioritize meaningful behaviors, edge cases,
   and error paths. Do not add tests solely for logic-free getters/setters or chase coverage percentages with
   assertion-free tests.
4. Add or update focused tests for the verified gaps. Avoid changing production code unless a test demonstrates a
   directly related defect; keep any such fix minimal and covered by a regression test.
5. Rerun the same package test and coverage scope. Also run the relevant style check and inspect the final diff.
   Preserve unrelated working-tree changes.
6. For the logic-free getter/setter, see the instructions in `.github/instructions/test.instructions.md`. 
   If a getter/setter is later modified to include meaningful behavior, remove the `@codeCoverageIgnore` tag and cover the new behavior with tests.

Success means the package test suite passes, new tests assert meaningful behavior for identified gaps, and coverage
results are reported for the package's source. If a coverage driver or required external service is unavailable,
state the limitation and do not claim the uncovered scope was verified. Summarize changed files, test results, and
coverage findings.
