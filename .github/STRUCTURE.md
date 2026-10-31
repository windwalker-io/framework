# `.github` Directory Guide

This directory centralizes everything that drives GitHub Copilot (and other AI coding agents) in this repository,
plus CI workflows. Use this file to decide **where new AI-facing content should live**.

## Layout

```
.github/
  copilot-instructions.md    # Repo-wide instructions, always loaded by Copilot
  instructions/*.instructions.md  # Path-scoped rules, auto-attached via `applyTo` glob
  prompts/*.prompt.md        # Reusable, explicitly-invoked prompt templates (slash commands)
  skills/<name>/SKILL.md     # On-demand, model-discovered deep-dive workflows
  docs/*.md                  # Long-form reference docs, linked-to (not auto-loaded)
  workflows/*.yml            # GitHub Actions CI
```

Root-level `/AGENTS.md` is the canonical, human-oriented agent guide and is explicitly referenced from
`copilot-instructions.md`. It is not duplicated here.

## How each asset type is loaded (per GitHub Copilot's documented behavior)

| Asset | Loaded when | Best for |
| --- | --- | --- |
| `copilot-instructions.md` | **Always**, for every Copilot Chat/agent request in this repo | Short, high-level, repo-wide conventions and an index pointing elsewhere. Keep it minimal — it costs context on *every* request. |
| `instructions/*.instructions.md` | **Automatically**, only when the files Copilot is editing/viewing match the `applyTo` front-matter glob | Mechanical, "must always be true" rules tied to a file type or path (code style, test style, lint conventions). No need to ask for these — they just apply. |
| `prompts/*.prompt.md` | **On explicit invocation** (e.g. `/prompt-name` in Copilot Chat) | Repeatable, parameterized tasks a human deliberately triggers (scaffolding, release notes, migration scripts). Not used for passive/background rules. |
| `skills/<name>/SKILL.md` | **On demand**, chosen by the model based on the skill's `description` front-matter matching the current task | Complex, multi-step domain workflows that may need accompanying resources/scripts/examples, and that shouldn't cost context unless actually relevant. |
| `docs/*.md` | **Never automatically** — must be linked explicitly from an instructions file, skill, or `copilot-instructions.md` | Longer-form rationale, full rule tables, "why", and anything that would bloat an instructions/skill file if inlined. |

This mirrors GitHub's own recommended split:
- [Repository custom instructions](https://docs.github.com/en/copilot/how-tos/configure-custom-instructions/add-repository-instructions) —
  one always-on `copilot-instructions.md`, kept short, for conventions that apply to *every* request.
- [Path-specific custom instructions](https://docs.github.com/en/copilot/how-tos/configure-custom-instructions/add-repository-instructions#adding-path-specific-custom-instructions) —
  `*.instructions.md` files with `applyTo` globs, for rules scoped to specific file types/paths.
- [Prompt files](https://code.visualstudio.com/docs/copilot/customization/prompt-files) —
  `*.prompt.md`, for reusable, manually-invoked task templates (VS Code/Copilot Chat "slash commands").
- [Agent Skills](https://docs.github.com/en/copilot/concepts/agents/about-agent-skills) —
  `SKILL.md` bundles, discovered and loaded by the agent on demand based on their description, for larger
  workflows that benefit from extra files alongside the instructions.
- Supplementary docs are explicitly **not** auto-loaded by any of the above; GitHub's guidance is to keep
  always-on/auto-attached files short and `Markdown link` out to deeper reference material instead of inlining it.

## Decision guide for new content

1. **Does every single Copilot request in this repo need to know it?**
   → Add a short bullet (or link) to `copilot-instructions.md`. Do not inline long explanations there.
2. **Does it only matter when touching certain files/paths, and should apply without being asked?**
   → Add/extend an `instructions/*.instructions.md` file with an `applyTo` glob front-matter.
3. **Is it a deliberately-triggered, repeatable task a human runs on demand?**
   → Add a `prompts/*.prompt.md` file.
4. **Is it a non-trivial, multi-step workflow the agent should discover and run autonomously, possibly with helper
   scripts/resources?**
   → Add a new `skills/<name>/SKILL.md` (optionally with sibling resource files in the same folder).
5. **Is it long-form rationale, a full reference table, or "why" context that would bloat an instructions/skill
   file?**
   → Add it to `docs/*.md`, then:
   - Link to it from whichever instructions file, skill, or `copilot-instructions.md` entry needs the deeper
     context (a doc nothing links to will never be discovered, since `docs/*.md` is never auto-loaded).
   - Add a one-line bullet to the **"Docs Reference Map"** section in `copilot-instructions.md` (feature/topic →
     doc path), so the agent knows to proactively check it for matching tasks.
   - Add a row to the contents table in `docs/README.md`.

When in doubt, prefer the narrowest scope (instructions > skill > copilot-instructions.md) for anything
auto-loaded, and move detailed prose into `docs/` either way.

## Current contents

- `instructions/cs.instructions.md` — PHP-CS-Fixer-backed code style rules (applies to all PHP files).
- `instructions/dependencies.instructions.md` — package dependency checks and root Composer synchronization (PHP / Composer manifests).
- `instructions/instruction.instructions.md` — how to place new rules across instructions / skills / docs.
- `instructions/test.instructions.md` — PHPUnit style and missing-test coverage rules (applies to test files).
- `prompts/complete-test-and-coverage.prompt.md` — inspect a package's coverage and add meaningful missing tests.
- `prompts/refactor-test-code-style.prompt.md` — refactor PHPUnit test style, structure, and lifecycle hook naming.
- `skills/test/SKILL.md` — end-to-end workflow for adding/running PHPUnit tests in this monorepo.
- `docs/code-style.md` — full PHP-CS-Fixer ruleset explanation/rationale. See `docs/README.md` for the docs index.
- `docs/database-testing.md` — Setting up MySQL/DSN for real-database tests and writing safe DB tests.
- `docs/testing.md` — PHPUnit execution and coverage setup, scoped reports, and finding missing tests.
