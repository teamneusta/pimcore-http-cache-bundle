---
name: review
description: Use when reviewing code changes — PR review, branch diff review, or pre-commit review. Performs systematic check for dead code, architecture compliance, documentation, test coverage, security, performance, BC breaks, and static analysis.
allowed-tools: Read, Grep, Glob, Bash(git *), Bash(composer *)
argument-hint: "[branch-or-pr-or-file]"
---

# Code Review

Systematic code review for this project. You are acting as an independent code reviewer — be thorough, critical, and constructive.

## Review Process

```
1. SCOPE: Understand what changed and why
2. CHECK: Run through all review categories
3. REPORT: Output findings with severity and locations
4. SUGGEST: Provide concrete fixes, not vague advice
```

### Step 1: Understand the Scope

```bash
# For branch review (compare against main)
git diff main...HEAD --stat
git log main..HEAD --oneline

# For specific commit
git show <commit> --stat

# For unstaged changes
git diff --stat
```

Read the changed files. Understand the intent before judging the implementation.

### Step 2: Run All Review Categories

Work through each category in the checklist below. For each finding, note:
- **File and line number** (e.g., `src/Cache/Foo.php:42`)
- **Severity**: BLOCKER / WARNING / SUGGESTION
- **Category**: Which checklist item
- **Description**: What's wrong and why
- **Fix**: Concrete suggestion

### Step 3: Output Format

```
## Review Summary
X blocker(s), Y warning(s), Z suggestion(s)

## Blockers
- `src/File.php:42` — [category] Description. Fix: ...

## Warnings
- `src/File.php:15` — [category] Description. Fix: ...

## Suggestions
- `src/File.php:78` — [category] Description. Fix: ...

## Checklist
- [x] Dead code
- [x] Architecture compliance
- [ ] Documentation — CHANGELOG needs update
- [x] Test coverage
...
```

---

## Review Checklist

See `checklist.md` in this directory for the full detailed checklist with examples.

### Quick Reference

| # | Category | Severity | Check |
|---|----------|----------|-------|
| 1 | Dead code | WARNING | Unused classes, methods, imports, variables |
| 2 | Architecture | BLOCKER | Decorator pattern, immutability, single-method interfaces |
| 3 | Documentation | WARNING | CHANGELOG, doc/, PHPDoc, README sync |
| 4 | Test coverage | BLOCKER | Every change has a test, conventions followed |
| 5 | Security | BLOCKER | Input validation, injection, OWASP |
| 6 | Performance | WARNING | N+1, unnecessary loops, missing early returns |
| 7 | BC breaks | BLOCKER | Public API changes, removed services, config |
| 8 | Static analysis | WARNING | PHPStan level 8, CS fixer compliance |

### Severity Guide

- **BLOCKER** — Must fix before merge. Bugs, security issues, BC breaks, missing tests.
- **WARNING** — Should fix. Dead code, missing docs, performance concerns.
- **SUGGESTION** — Nice to have. Style improvements, minor refactoring.

---

## Role Separation

When acting as reviewer, you are NOT the author. This means:
- Do not fix issues yourself — report them
- Do not assume intent — ask if unclear
- Do not approve your own code — if you wrote it, you cannot review it
- Be specific — "this looks wrong" is not a review comment
- Be constructive — explain WHY something is an issue, not just THAT it is
