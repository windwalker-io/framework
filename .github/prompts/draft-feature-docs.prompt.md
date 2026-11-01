---
---

For a newly created feature in this Windwalker Framework monorepo, write a temporary draft document that the author can
later move into the official documentation.

1. Determine the scope from the user's description, the selected files, or the changes made earlier in the current task.
   Only document features that are new (new classes, methods, options, attributes, etc.); skip pure refactors, bug
   fixes, and internal-only changes.
2. Identify the package the feature belongs to and create the draft under `packages/{name}/docs/`
   (create the directory if missing). Use a short kebab-case file name that describes the feature, e.g.
   `cursor-pagination.md`. If one feature spans multiple packages, put the draft in the package that owns the
   user-facing API.
3. Write the draft from the end user's point of view:
   - **Introduction**: what the feature is, what problem it solves, and when to use it.
   - **Usage**: how to use or operate it, with minimal, runnable PHP examples that follow the repository code style
     (`.github/instructions/cs.instructions.md`) and prefer current APIs (e.g. `***Options` classes over legacy
     flags).
   - **Options / behaviors**: notable parameters, defaults, edge cases, and limitations that users need to know.
   - Mention deprecated or replaced APIs only when users need to migrate.
4. Write as if the content is one section of a long document. Assume surrounding sections already explain
   framework- or package-specific concepts (e.g. DI, ORM, Query, Options classes). Do not re-explain that background;
   refer to it by name only. Do not add document-level boilerplate such as a table of contents, a global overview, or
   a conclusion. Start with a heading suitable for nesting under an existing chapter.
5. Verify every example against the actual source (signatures, namespaces, return types); do not document behavior
   that does not exist. Do not modify source code or tests as part of this task.
6. Mark the file as temporary with a short note at the very top (e.g. `> Draft: to be merged into the official docs.`)
   so it is clear it should be moved and then deleted.

Success criteria: the draft is located in `packages/*/docs/`, covers introduction and end-user usage, matches the real
API, and reads naturally as a chapter inside a longer document without repeating known background.
