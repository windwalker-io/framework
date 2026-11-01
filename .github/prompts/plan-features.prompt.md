---
---
Plan or research a new feature, or a refactor, for this repository. Do not implement it; produce an analysis and a
recommendation.

## Requirements

1. **Analyze the existing architecture first.** Read the relevant package(s) under `packages/`, their style and
   philosophy (see `/AGENTS.md` and `/.github/instructions/cs.instructions.md`: DI over globals/facades, property
   hooks, `***Options` classes, no magic methods, etc.), and identify how the change should fit in, plus which
   existing APIs, tests, and docs it touches.
2. **Research how other frameworks and systems solve the same problem.** Cover a diverse set of references:
   - PHP frameworks: Laravel, Symfony, and others where relevant.
   - For lower-level features, also look beyond PHP: Node.js, Go, Rust, TypeScript, Java, and any other ecosystem
     with a notable solution.
   - Cite concrete APIs, source files, or documentation for each reference.
3. **For every researched reference, list its pros and cons**, including how well it would map onto this
   codebase's conventions and PHP's constraints (PHP ≥ 8.4.6).
4. **Give a recommendation**: the proposed design, why it was chosen over the alternatives, the trade-offs, and
   the backward-compatibility impact (deprecations, migration path).
5. **Outline an implementation plan**: affected packages and files, step order, risks, open questions, and the
   tests to add or update.

## Success criteria

- The analysis is grounded in the repository's actual code, not assumptions.
- At least two PHP references and at least two non-PHP references are compared when the topic allows; if a
  reference is not applicable, say why.
- A comparison table summarizes the pros and cons of each reference.
- The final recommendation is explicit, justified, and consistent with the project's code style and conventions.
- Unresolved decisions are listed as questions for the user instead of being silently assumed.
