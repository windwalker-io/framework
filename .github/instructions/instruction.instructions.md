---
applyTo: ".github/**,AGENTS.md"
---

# Maintaining AI Instructions

When the user asks to "add rules to instructions" (or skills / docs), it means **add the rules to the AI-facing
documentation in the appropriate place**, not to stuff everything into `instructions/*.instructions.md`.

Split the content by its nature, following the decision guide in `/.github/README.md`:

- **Always-needed, repo-wide, short** → a bullet or link in `copilot-instructions.md`.
- **Mechanical rules tied to a file type/path** → `instructions/*.instructions.md` with an `applyTo` glob. Keep it
  concise: rules and short Bad/Good examples only.
- **Repeatable, human-triggered tasks** → `prompts/*.prompt.md`.
- **Multi-step workflows or domain procedures** → `skills/<name>/SKILL.md`.
- **Long-form rationale, full rule tables, background** → `docs/*.md`. Then:
  - Link it from the related instructions/skill file.
  - Add a bullet to the "Docs Reference Map" in `copilot-instructions.md` (keep alphabetical).
  - Add a row to the table in `docs/README.md`.

A single request may need to be split across several of these. Do not duplicate content between them; link instead.
Update the "Current contents" list in `/.github/README.md` when adding new files.
