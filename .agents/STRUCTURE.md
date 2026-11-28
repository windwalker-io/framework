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
  agents/*.agent.md                # Specialized agent profiles
  instructions/*.instructions.md  # Path-scoped rules, auto-attached via `applyTo` glob
  prompts/*.prompt.md              # Reusable, explicitly-invoked prompt templates
  skills/<name>/SKILL.md           # On-demand, model-discovered workflows
  docs/*.md                        # Long-form reference docs, linked-to (not auto-loaded)
  main.instructions.md             # Shared repo-wide guidance
  STRUCTURE.md                     # This content-placement guide
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
  one always-on `.github/copilot-instructions.md` entry point for conventions that apply to *every* request.
- [Path-specific custom instructions](https://docs.github.com/en/copilot/how-tos/configure-custom-instructions/add-repository-instructions#adding-path-specific-custom-instructions) —
  `.agents/instructions/*.instructions.md` files with `applyTo` globs, for rules scoped to specific file types/paths.
- [Prompt files](https://code.visualstudio.com/docs/copilot/customization/prompt-files) —
  `.agents/prompts/*.prompt.md`, for reusable, manually-invoked task templates (VS Code/Copilot Chat "slash commands").
- [Agent Skills](https://docs.github.com/en/copilot/concepts/agents/about-agent-skills) —
  `.agents/skills/<name>/SKILL.md` bundles, discovered and loaded by the agent on demand based on their description,
  for larger workflows that benefit from extra files alongside the instructions.
- Supplementary docs are explicitly **not** auto-loaded by any of the above; GitHub's guidance is to keep
  always-on/auto-attached files short and `Markdown link` out to deeper reference material instead of inlining it.

## Decision guide for new content

1. **Does every single Copilot request in this repo need to know it?**
   → Add shared guidance to `.agents/main.instructions.md`, which is imported by both entry points. Keep
   `.github/copilot-instructions.md` minimal and do not inline long explanations there.
2. **Does it only matter when touching certain files/paths, and should apply without being asked?**
   → Add/extend a `.agents/instructions` file with an `applyTo` glob front-matter.
3. **Is it a deliberately-triggered, repeatable task a human runs on demand?**
   → Add a `.agents/prompts` file.
4. **Is it a non-trivial, multi-step workflow the agent should discover and run autonomously, possibly with helper
   scripts/resources?**
   → Add a new `.agents/skills` bundle (optionally with sibling resource files in the same folder).
5. **Is it long-form rationale, a full reference table, or "why" context that would bloat an instructions/skill
   file?**
   → Add it to `.agents/docs`, then:
   - Link to it from whichever instructions file, skill, or `AGENTS.md` entry needs the deeper
     context (a doc nothing links to will never be discovered, since `docs` is never auto-loaded).
   - Add a one-line bullet to the **"Docs Reference Map"** section in `.agents/main.instructions.md` (feature/topic →
     doc path), so the agent knows to proactively check it for matching tasks.
   - Add a row to the contents table in `.agents/docs/README.md`.

When in doubt, prefer the narrowest scope (instructions > skill > shared repo guidance) for anything
auto-loaded, and move detailed prose into `docs` either way.

## Current contents

- `.agents/main.instructions.md` — shared repo-wide instructions imported by `AGENTS.md` and Copilot.
- `.agents/agents/` — specialized agent definitions.
- `.agents/instructions/` — path-scoped rules and guidance on maintaining instructions.
- `.agents/prompts/` — explicitly-invoked workflows for testing, documentation, planning, and worktree tasks.
- `.agents/skills/` — on-demand workflows; currently includes the PHPUnit testing skill.
- `.agents/docs/` — code-style and testing references, indexed in `.agents/docs/README.md`.
- `.github/` — Copilot entry point, MCP configuration, and GitHub Actions workflows.
