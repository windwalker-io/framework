Following the repository's testing instructions and test skills, refactor the PHPUnit test code style and structure
within the specified scope `packages/*/test/`, including the naming and calling conventions of test methods and
lifecycle hooks.

Before starting, read `.github/instructions/test.instructions.md`, `.github/instructions/cs.instructions.md`, and
`.github/skills/test/SKILL.md`, and consult `.github/docs/testing.md` if necessary. Confirm the user-specified package,
file, or test scope; if not specified, review changes or context to define the minimum reasonable scope. Do not rewrite
the entire monorepo's tests without confirmation.

Refactoring Requirements:

- Follow the repository's existing PHP/PHPUnit versions and testing conventions; retain `declare(strict_types=1);`,
  correct namespaces, imports, and type declarations.
- Actually rename test methods; do not only check formatting. Follow the naming rules in
  `.github/skills/test/SKILL.md`:
  - Remove the `test` prefix from every test method, mark it with `#[Test]`, and import
    `PHPUnit\Framework\Attributes\Test`.
  - Use a camelCase name that describes the behavior under test (subject + action + expected outcome), e.g.
    `testRead` → `readReturnsFileContents`, `testDelete` → `deleteRemovesDirectoryRecursively`. Vague names such as
    `testFoo`, `testItems`, or names that only repeat the method under test are not acceptable.
  - Split a test that verifies several independent scenarios into one test per scenario, each with its own name,
    without weakening or dropping assertions. Keep them together only when they are steps of a single scenario.
  - Data provider methods must end with `Provider` and be referenced via `#[DataProvider('...')]`.
  - Do not rename tests inherited from or overriding a parent/trait/interface when the name is part of its contract,
    and do not change test meaning merely for renaming.
  - After renaming, grep for the old names (`--filter`, docs, `@depends`, `#[Depends]`, `#[DataProvider]`
    references) and update every usage.
- For lifecycle setup/teardown, prioritize using PHPUnit's attributes like `#[Before]`, `#[After]`, `#[BeforeClass]`,
  and `#[AfterClass]`, replacing vague `setUp()`/`tearDown()` names with names that express the actual behavior.
- Before migrating lifecycle hooks, trace the initialization/cleanup order of parent classes, traits, and inheritance;
  retain necessary parent calls, execution order, and resource cleanup. Before removing empty hooks or useless fixtures,
  ensure they do not obscure or replace inherited behavior.
- Improve structure with meaningful behavioral assertions; use data providers when the same behavior is validated across
  multiple inputs. Do not merge different scenarios, weaken assertions, or change test meaning to shorten code.
- Follow PHPUnit conventions in the instructions, such as exception message matchers, mock invocation count matchers,
  and testing and coverage rules for logic-free getters/setters.
- Do not modify the behavior of production `src/`. If adjustments to the test support code are necessary, limit them to
  the scope of the test support and explain the reasons; do not use test refactoring to change public APIs or B/C.
- Retain existing uncommitted changes from users; do not handle existing issues unrelated to the target, and do not
  perform destructive cleanup.
- Remove unnecessary `__DEPLOY_VERSION__` and docblock description placeholders with the same name as functions; see
  reference.

Large-scope handling:

- If the scope is large (many files or many subdirectories, e.g. a whole package or several packages), do not process
  it all in one pass. Split it into multiple iterations, one subdirectory (or small batch of files) per iteration, and
  finish each one (inspect, change, verify) before starting the next.
- Keep a short checklist of the subdirectories/files and mark each as done, so most files are genuinely inspected
  rather than skimmed, and context stays small enough to keep focus.
- Read only the files for the current iteration; avoid carrying earlier iterations' file contents forward.
- Run the targeted check/tests per iteration, then do the final overall verification and summary at the end.

Upon completion, check for differences, perform PHP syntax/lint and format checks on modified files, and execute the
minimum PHPUnit selector that covers the changes. If tests require unconfigured repositories or external services, do
not falsely claim a pass; try appropriate temporary PHPUnit or CLI settings as instructed, otherwise clearly list the
scope of failures, skips, and incomplete results.

Success criteria: No test method in the scope still uses the `test` prefix or a vague name, the target test structure and naming are clear and consistent, the lifecycle behavior is the same as
the original test, the assertion meaning is not weakened, the scope of modification is controlled, and the actual
inspection and test results have been reported.
