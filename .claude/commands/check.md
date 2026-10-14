---
description: Run tests/static-check.php and explain the result
allowed-tools: Bash(php tests/static-check.php), Bash(php -l:*), Read, Grep, Glob
---

Run from the repository root:

```bash
php tests/static-check.php
```

Then answer the user briefly:

1. Result: "all passed (N checks)" or "K checks failed".
2. For every failed check (lines with `✘`): what it checks (sections 1–6 are described in `tests/README.md`), the file and line, why it is an error and how to fix it. Refer to the rules in `docs/ai/AI_I18N.md` (translations, text domains, Cyrillic), `docs/ai/AI_VERSIONING.md` (versions, CHANGELOG), `docs/ai/AI_TESTING.md`.
3. Propose a code fix. **Never weaken or remove a check** to make it pass. If the error is in `.pot`/`.po`/`.mo`, explain how to regenerate the translations (`AI_I18N.md`).
4. If `php` is not found, say that PHP 8.3+ is required and suggest that the user runs the command.

Do not change files without confirmation unless the user asked for it. Extra instructions from the user: $ARGUMENTS
