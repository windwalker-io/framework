# Agent Guidance Structure

AI-agent guidance lives under the repository's `.agents/` directory, while `.github/` contains the Copilot entry point
and GitHub Actions workflows. Use this file to decide **where new AI-facing content should live**.

## Layout

```
AGENTS.md                         # Canonical human-oriented agent guide
.github/
  copilot-instructions.md         # Copilot entry point; imports .agents/main.instructions.md
  workflows/*.yml                 # GitHub Actions CI
.agents/
  agents/*.agent.md               # Specialized agent profiles
  instructions/*.instructions.md # Path-scoped rules, auto-attached via `applyTo` glob
  prompts/*.prompt.md             # Reusable, explicitly-invoked prompt templates
  skills/<name>/SKILL.md          # On-demand, model-discovered workflows
  docs/*.md                       # Long-form reference docs, linked-to (not auto-loaded)
  main.instructions.md            # Shared repo-wide guidance
  STRUCTURE.md                    # This content-placement guide
```

`AGENTS.md` and `.github/copilot-instructions.md` both load `.agents/main.instructions.md`; keep shared guidance there
instead of duplicating it in the entry points.

## How each asset type is loaded (per GitHub Copilot's documented behavior)

| Asset | Loaded when | Best for |
| --- | --- | --- |
| `.github/copilot-instructions.md` | **Always**, for every Copilot Chat/agent request in this repo | Imports shared repo-wide guidance; keep detailed rules in `.agents/main.instructions.md`. |
| `.agents/instructions` | **Automatically**, when files match an `applyTo` front-matter glob | Mechanical, "must always be true" rules tied to a file type or path (code style, test style, lint conventions). |
| `.agents/prompts` | **On explicit invocation** (e.g. `/prompt-name` in Copilot Chat) | Repeatable tasks a human deliberately triggers. Not used for passive/background rules. |
| `.agents/skills` | **On demand**, chosen by the model based on the skill's `description` front-matter matching the current task | Complex, multi-step workflows that may need accompanying resources and should not cost context unless relevant. |
| `.agents/docs` | **Never automatically** — must be linked explicitly from an instructions file, skill, or entry point | Longer-form rationale, full rule tables, and "why" context that would bloat an instructions/skill file if inlined. |
| `.agents/agents` | **When selected for a task** | Specialized agent definitions for bounded tasks. |

This mirrors GitHub's own recommended split:
- [Repository custom instructions](https://docs.github.com/en/copilot/how-tos/configure-custom-instructions/add-repository-instructions) —
  one always-on `../.github/copilot-instructions.md`, kept short, for conventions that apply to *every* request.
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
   → Add a short bullet (or link) to `../.github/copilot-instructions.md`. Do not inline long explanations there.
2. **Does it only matter when touching certain files/paths, and should apply without being asked?**
   → Add/extend an `instructions` file with an `applyTo` glob front-matter.
3. **Is it a deliberately-triggered, repeatable task a human runs on demand?**
   → Add a `prompts` file.
4. **Is it a non-trivial, multi-step workflow the agent should discover and run autonomously, possibly with helper
   scripts/resources?**
   → Add a new `skills` (optionally with sibling resource files in the same folder).
5. **Is it long-form rationale, a full reference table, or "why" context that would bloat an instructions/skill
   file?**
   → Add it to `docs`, then:
   - Link to it from whichever instructions file, skill, or `../.github/copilot-instructions.md` entry needs the deeper
     context (a doc nothing links to will never be discovered, since `docs` is never auto-loaded).
   - Add a one-line bullet to the **"Docs Reference Map"** section in `../.github/copilot-instructions.md` (feature/topic →
     doc path), so the agent knows to proactively check it for matching tasks.
   - Add a row to the contents table in `docs/README.md`.

When in doubt, prefer the narrowest scope (instructions > skill > copilot-instructions.md) for anything
auto-loaded, and move detailed prose into `docs` either way.

## Current contents

- `instructions/config-sync.instructions.md` — keep composer / CI / PHPUnit / docker configs and related packages in sync.
- `instructions/cs.instructions.md` — PHP-CS-Fixer-backed code style rules (applies to all PHP files).
- `instructions/dependencies.instructions.md` — package dependency checks and root Composer synchronization (PHP / Composer manifests).
- `instructions/instruction.instructions.md` — how to place new rules across instructions / skills / docs.
- `instructions/test.instructions.md` — PHPUnit style and missing-test coverage rules (applies to test files).
- `instructions/worktree.instructions.md` — worktree mode workflow: custom naming, commit every change, squash-merge.
- `prompts/complete-test-and-coverage.prompt.md` — inspect a package's coverage and add meaningful missing tests.
- `prompts/refactor-test-code-style.prompt.md` — refactor PHPUnit test style, structure, and lifecycle hook naming.
- `prompts` — commit pending worktree changes and squash-merge them into the working branch.
- `prompts` — analyze a new feature or refactor, research alternatives, and produce a recommendation and implementation plan.
- `skills/test/SKILL.md` — end-to-end workflow for adding/running PHPUnit tests in this monorepo.
- `docs/code-style.md` — full PHP-CS-Fixer ruleset explanation/rationale. See `docs` for the docs index.
- `docs/database-testing.md` — Setting up MySQL/DSN for real-database tests and writing safe DB tests.
- `docs/testing.md` — PHPUnit execution and coverage setup, scoped reports, and finding missing tests.
