# CLAUDE.md

> File for Claude Code: it reads `CLAUDE.md` automatically and loads the files referenced with `@`.
> The context is not duplicated here — it lives in the files below.

@AGENTS.md
@AI.md

## For Claude Code

- Read the detailed `docs/ai/AI_*.md` files per task (order in `AGENTS.md`), not all at once.
- The code is the source of truth. If the docs disagree with the code, tell the user and fix the docs.
- After a change: `php tests/static-check.php`; on a test site — `wp eval-file tests/smoke-test.php`. No terminal or WordPress — tell the user what to run and what to check manually.
- Commands: `/check` — run `static-check` and explain the result; `/new-ajax <name> <what it does>` — add an AJAX action by the checklist. After every PHP edit a hook (`.claude/settings.json`) runs `php tests/static-check.php` itself; if it reports an error, fix the code, not the test.
- City selection / several regions — read `docs/ai/AI_EXTENDING.md`, section 11 first (and ask about WP Multisite).
- The Russian version of the project is the `ru` branch on GitHub.
