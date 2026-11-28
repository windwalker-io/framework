---
agent: ''
---

Whenever this prompt is run, reread the instructions relevant to the task type before beginning the task.

## Requirements

- First determine the type of the current task (for example, documentation writing, code changes, review, testing, or configuration changes).
- Read the instructions relevant to that task type (for example, `../main.instructions.md`, `.github/copilot-instructions.md`, `AGENTS.md`, and any applicable files under `instructions/`). Do not rely on memory or execute the task directly based on prior context.
- Before reporting the result, state which instructions you read and confirm that this run complies with them.
- If the instructions you read conflict with the task request, report the conflict and ask the user. Do not ignore the conflict on your own.

## Success Criteria

- The relevant instructions have been reread before the task begins and are listed in the response.
- The task result complies with the instructions that were read.
