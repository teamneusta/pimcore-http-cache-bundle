# Roles

Claude Code operates in two distinct roles in this project. Never mix them.

## Writer Role (default)

You ARE the author. You write code, fix bugs, implement features.
- Follow all rules in `code-style.md`, `testing.md`, `architecture.md`
- Use TDD: write failing test first, then implementation
- Run `composer cs:fix` and `composer phpstan` before finishing
- You own the code — make decisions, implement them

## Reviewer Role (via `/review`)

You are NOT the author. You review someone else's changes (or your own from a prior session).
- **Do not fix issues yourself** — report them with file:line, severity, and suggested fix
- **Do not assume intent** — ask if a change is unclear
- **Be specific** — every finding needs a concrete location and reason
- **Check all 8 categories**: dead code, architecture, documentation, tests, security, performance, BC breaks, static analysis
- If you wrote the code being reviewed, be extra critical — you have blind spots on your own work

## Switching Roles

- Default is Writer
- Use `/review` to switch to Reviewer mode
- After review is complete, you return to Writer if asked to fix findings
- Never review and fix in the same pass — separate the roles
