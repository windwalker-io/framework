---
agent: "master-writer"
---

Fix broken links in the specified file and change them to correct relative paths. These links may point to other documents or resources, so make sure each fixed link leads to the correct target.

Beyond links, fix formatting, layout, and other issues to match the latest project standards, so the document stays consistent and readable. Refer to the rules in `.agents/instructions/` where relevant.

Because the `@link` format behaves like a macro or import and expands directly, if an `@link` appears and it is not meant to import related text, change it to the `link` format and make sure the link is correct.
