# Project AI Instructions

This Laravel project uses the `{{STARTERKIT_DIRECTORY}}/` Git submodule as its agentic-coding foundation and execution contract.

Before planning or changing code:

1. Read `{{STARTERKIT_DIRECTORY}}/AGENTS.md` in full as the primary contract.
2. Read only the rules in `{{STARTERKIT_DIRECTORY}}/docs/rules/` routed to the task.
3. Treat `docs/...` mentioned by the starter contract as `{{STARTERKIT_DIRECTORY}}/docs/...`; feature paths such as `app/`, `routes/apps/`, `resources/views/apps/`, `database/migrations/apps/`, `tests/`, and `issues/` are at this Laravel root.
4. Do not change `{{STARTERKIT_DIRECTORY}}/` for a project feature. Changes there are universal starter improvements only.

Developers provide business context. The agent applies the required workflow, security, performance, validation, audit, pagination, UI, testing, and file ownership standards without asking the developer to repeat them.
