# Guidance for writing instructions

## Purpose

When a user asks to add rules to `instructions`, `skills`, or `docs`, that does not mean everything should be put in `instructions`. Instead, divide the content among the most suitable locations according to the nature and frequency of use of the rules.

## Classification rules

When a user asks to "add rules to instructions / skills / docs", use these criteria:

- `instructions`: Repository-level rules and content-routing guidance
- `skills`: Shared skills and templates
- `docs`: Long-form documentation and templates

For details, see `../main.instructions.md#guidance-document-directory`

## Editing principles

- File links:
  - Use the `path` format for important file links or guidance.
  - When a heading is needed, use a Markdown link: `[display text](relative path)`.
  - In general prose, decide whether to link to a file based on context. Use backticks when simply mentioning a filename or file type.
  - Use the `<URL>` format for remote URLs.
- Use concise bullet points; avoid too much background unless the behavior has a specific context or constraint.
- Avoid sensitive information, product confidential information, deployment settings, and keys.

### Skills writing principles

See the public guidelines:

- <https://www.skillsdirectory.com/docs/writing-effective-skills> Writing Effective Skills
- <https://www.skillsdirectory.com/docs/skill-md-format> Skill MD Format
- <https://www.skillsdirectory.com/docs/skill-file-structure> Skill File Structure

## Validation

- The content remains Markdown-only.
- The guidance remains general and reusable.
- The saved content remains aligned with the shared company knowledge base.
- The project does not gradually become a private database for a single product or project.
