## Working with Claude Code

This project is set up for AI-assisted development with [Claude Code](https://docs.anthropic.com/en/docs/claude-code). All configuration is committed to the repo — once you have Claude Code installed, everything works automatically.

### Getting Started

1. Install Claude Code: https://docs.anthropic.com/en/docs/claude-code
2. Open a terminal in the project root
3. Run `claude` — it will automatically load the project context

Claude reads `CLAUDE.md` and all files in `.claude/rules/` and `.claude/skills/` at the start of every session. No manual setup needed.

### Project Structure

```
CLAUDE.md                          # Project overview, quick reference, conventions
.claude/
├── rules/                         # Always loaded — project conventions
│   ├── code-style.md              # PHP coding standards with examples
│   ├── testing.md                 # PHPUnit + Prophecy conventions
│   ├── architecture.md            # Decorator chains, adapters, lifecycle flows
│   ├── static-analysis.md         # PHPStan level 8 requirements
│   ├── bundle-usage.md            # How the bundle works for developers
│   └── roles.md                   # Writer vs reviewer role separation
├── skills/                        # On-demand — invoke with /command
│   ├── review/                    # /review — code review checklist
│   ├── php-best-practices/        # /php-best-practices — PHP 8.x audit
│   ├── php-pro/                   # /php-pro — senior PHP patterns
│   ├── tdd/                       # /tdd — test-driven development
│   ├── debug/                     # /debug — systematic debugging
│   ├── brainstorm/                # /brainstorm — ideas to designs
│   ├── code-review/               # /code-review — handle review feedback
│   ├── web-search/                # /web-search — web search via inference.sh
│   ├── writing-skills/            # /writing-skills — create new skills
│   ├── humanizer/                 # /humanizer — remove AI writing patterns
│   └── context7/                  # /context7 — live library docs lookup
└── settings.local.json            # Personal settings (gitignored)
```

### Rules vs Skills

**Rules** (`.claude/rules/`) are loaded automatically at the start of every session. They define how Claude should behave in this project — coding standards, testing conventions, architecture patterns. Claude follows these without being asked.

**Skills** (`.claude/skills/`) are loaded on demand. You invoke them with a slash command (e.g., `/review`) or Claude may auto-trigger them based on context. Each skill is a focused workflow for a specific task.

### Development Workflow

#### 1. Writing Code (Writer Role)

Claude's default role is code writer. It follows all rules automatically.

```
you:    "Add a new cache type for product categories"
claude: [Reads rules, understands conventions, follows TDD]
        — Writes failing test first
        — Implements minimal code to pass
        — Runs composer cs:fix and phpstan
        — Commits when asked
```

For test-driven development, invoke `/tdd` to enforce the red-green-refactor cycle strictly.

#### 2. Reviewing Code (Reviewer Role)

After writing code, use `/review` to switch Claude into reviewer mode. The reviewer checks 8 categories:

1. Dead code — unused classes, methods, imports
2. Architecture — final classes, immutability, patterns
3. Documentation — CHANGELOG, doc/, PHPDoc sync
4. Test coverage — every change has a test
5. Security — input validation, injection risks
6. Performance — N+1 queries, early returns
7. BC breaks — public API changes
8. Static analysis — PHPStan level 8, CS fixer

```
you:    /review
claude: [Reviews all changes against main branch]
        — Reports findings as BLOCKER / WARNING / SUGGESTION
        — Lists file:line for each finding
        — Does NOT fix issues (that's the writer's job)
```

**Important**: The reviewer reports findings — it does not fix them. After the review, switch back to writer mode to address the findings. This separation prevents blind spots.

#### 3. Recommended Workflow

```
1. Start a session — Claude loads all rules automatically
2. Write code — follow TDD (or invoke /tdd for strict mode)
3. Run QA — composer cs:fix && composer phpstan && composer tests
4. Review — invoke /review to get an independent review
5. Fix findings — address blockers and warnings
6. Commit — ask Claude to commit when ready
```

#### 4. Debugging

When stuck on a bug, invoke `/debug` for the systematic debugging process:

```
you:    /debug "CacheTag throws exception for valid input"
claude: [Phase 1: Root cause investigation]
        [Phase 2: Pattern analysis]
        [Phase 3: Hypothesis and testing]
        [Phase 4: Implementation with failing test]
```

### Available Skills

| Command | When to Use |
|---------|-------------|
| `/review` | Review code changes before merging (you are the reviewer) |
| `/code-review` | Handle feedback received from external reviewers (you are the author) |
| `/tdd` | Enforce strict test-driven development |
| `/debug` | Systematic debugging of any issue |
| `/brainstorm` | Turn an idea into a design and spec |
| `/php-best-practices` | Audit code against 45+ PHP 8.x rules |
| `/php-pro` | Senior PHP patterns (Symfony, Laravel, async) |
| `/context7` | Look up current library documentation via Context7 API |
| `/web-search` | Search the web via inference.sh |
| `/humanizer` | Remove AI writing patterns from text |
| `/writing-skills` | Create new skills for the project |

### Keeping Things Updated

The Claude Code configuration is part of the codebase. **Treat it like code** — update it when the project changes.

#### When to Update CLAUDE.md

- New dependencies added or removed
- PHP version requirements changed
- CI/QA workflows changed
- Project structure changed significantly

#### When to Update Rules

- **code-style.md** — coding conventions changed (new CS fixer rules, etc.)
- **testing.md** — test framework or conventions changed
- **architecture.md** — new patterns introduced, decorator chains modified
- **static-analysis.md** — PHPStan level or config changed
- **bundle-usage.md** — new features, config options, events added
- **roles.md** — review process or role definitions changed

#### When to Update Skills

- Review checklist needs new categories
- TDD workflow adjusted for the team
- New tools or workflows adopted

#### How to Update

Edit the files directly and commit them. Claude will use the updated versions in the next session.

```bash
# Example: add a new rule
vim .claude/rules/new-convention.md
git add .claude/rules/new-convention.md
git commit -m "Add rule for new convention"
```

You can also ask Claude to update its own rules:

```
you:    "We decided to use PHPStan level 9 now. Update the rules."
claude: [Updates static-analysis.md, CLAUDE.md, and any affected skills]
```

Or ask Claude to remember something:

```
you:    "Remember that we always use DateTimeImmutable, never DateTime"
claude: [Saves to auto-memory for future sessions]
```

### Personal Settings

`.claude/settings.local.json` is gitignored — use it for personal preferences that shouldn't affect the team (e.g., tool permissions).

For shared team settings, use `.claude/settings.json` (committed to git).

### Creating New Skills

Use `/writing-skills` for guidance on creating project-specific skills. Skills live in `.claude/skills/<name>/SKILL.md` and are available to all team members once committed.

```bash
mkdir -p .claude/skills/my-skill
# Create .claude/skills/my-skill/SKILL.md with frontmatter + instructions
git add .claude/skills/my-skill/
git commit -m "Add my-skill"
```

### Tips

- **Be specific** — "Fix the bug in CacheTag::fromString when tag contains spaces" works better than "fix the bug"
- **Use TDD** — invoke `/tdd` for new features and bug fixes
- **Review before merging** — invoke `/review` to catch issues you might miss
- **Keep rules current** — if you notice Claude doing something wrong, update the rules so it doesn't happen again
- **One thing at a time** — don't ask Claude to implement a feature AND review it in the same session
