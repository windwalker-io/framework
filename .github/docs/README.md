# Docs Index

This folder holds **long-form, reference-style documentation** for both humans and AI agents. Unlike
`../instructions/*.instructions.md` or `../skills/<name>/SKILL.md`, nothing in here is loaded automatically by
Copilot — files here are only read when something else (an instructions file, a skill, `copilot-instructions.md`,
or a human) explicitly links to them.

See `../README.md` for the full explanation of how `instructions/` / `prompts/` / `skills/` / `docs/` differ and
when to use each.

## When to add a doc here

- The content is "why"/rationale, a full reference table, or background explanation — not a short actionable rule.
- Inlining it into an `instructions/*.instructions.md` file or a `SKILL.md` would make that file too long to stay
  skimmable.
- It's useful context on demand, but doesn't need to cost context on every matching request.

If instead the content is a short, always-applicable rule tied to a file pattern, put it in
`../instructions/*.instructions.md` with an `applyTo` front-matter glob, and only link to a doc here for the deeper
"why".

## Naming & structure

- One Markdown file per topic, named `kebab-case.md` (e.g. `code-style.md`).
- Start with a single `# Title` heading.
- Use relative links (e.g. `../../AGENTS.md`, `../../.php-cs-fixer.dist.php`) since this folder is nested two
  levels under the repository root (`.github/docs/`).
- Always add a one-line entry to the table below, and link to the doc from whichever instructions file, skill, or
  `copilot-instructions.md` entry needs the deeper context — a doc that nothing links to will not be discovered.
- Also add a bullet to the **"Docs Reference Map"** section in `../copilot-instructions.md` (feature/topic → doc
  path). That section is the one piece of this folder that *is* always loaded, so it's what makes a new doc
  discoverable without the agent having to stumble onto a stray link first.

## Contents

| Doc | Summary | Linked from |
| --- | --- | --- |
| [`code-style.md`](./code-style.md) | Full PHP-CS-Fixer ruleset: `@PER-CS` baseline, every project rule override, indentation/line-ending settings. | `../instructions/cs.instructions.md`, `../../AGENTS.md` |
| [`database-testing.md`](./database-testing.md) | Real-database test setup: MySQL/Docker, DSN constants, sequential runs, fixture and state-isolation rules. | `../instructions/test.instructions.md`, `../skills/test/SKILL.md` |
| [`testing.md`](./testing.md) | PHPUnit execution, coverage drivers and scoped reports, and filling missing test coverage. | `../instructions/test.instructions.md`, `../skills/test/SKILL.md`, `../../AGENTS.md` |
