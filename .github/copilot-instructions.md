# Copilot Instructions

## General Guidelines

See `/AGENTS.md` for general guidelines on how to use Copilot effectively.

See `/.github/README.md` for how this `.github` directory is organized (instructions / prompts / skills / docs)
and where to place new AI-facing content.

## Others

- Code Style
  - [cs.instructions.md](instructions/cs.instructions.md) - Advanced PHP code style rules.
  - [code-style.md](docs/code-style.md) - Full PHP-CS-Fixer ruleset rationale.
- Testing
  - [test.instructions.md](instructions/test.instructions.md) - Testing-specific code style and deprecated/preferred
    APIs.
  - [test skill](skills/test/SKILL.md) - Workflow for adding, updating, and running tests.
- [Instruction Maintenance](instructions/instruction.instructions.md) - Where to place new rules across
  instructions / skills / docs.

## Docs Reference Map

`/.github/docs/*.md` files are **not** auto-loaded — consult them proactively when a task touches the matching
feature/topic below. Keep this list alphabetical and append a new bullet whenever a new doc is added to
`/.github/docs/` (also update `/.github/docs/README.md`'s table at the same time).

- **Code style / PHP-CS-Fixer rules (`@PER-CS`, rule overrides, rationale)** → `/.github/docs/code-style.md`


