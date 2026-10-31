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
- Ensure test names clearly describe the behavior being validated, using explicit and searchable naming. Adhere to
  repository guidelines and use attributes like `#[Test]` and `#[DataProvider]`, avoiding changes to test meaning simply
  for renaming.
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

Upon completion, check for differences, perform PHP syntax/lint and format checks on modified files, and execute the
minimum PHPUnit selector that covers the changes. If tests require unconfigured repositories or external services, do
not falsely claim a pass; try appropriate temporary PHPUnit or CLI settings as instructed, otherwise clearly list the
scope of failures, skips, and incomplete results.

Success criteria: The target test structure and naming are clear and consistent, the lifecycle behavior is the same as
the original test, the assertion meaning is not weakened, the scope of modification is controlled, and the actual
inspection and test results have been reported.
