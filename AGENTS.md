# AGENTS.md

> Full project context for AI assistants is in [`AI.md`](AI.md) and its related documents.

## Why this file

Different AI assistants look for different context file names:

| Tool | Expected name |
|------------|---------------|
| Cursor | `.cursorrules` |
| Claude Code | `CLAUDE.md` |
| OpenAI Codex | `AGENTS.md` ← this file |
| Aider, Windsurf, universal | `AI.md` |

To avoid duplication, **all context lives in `AI.md` and `AI_*.md`**.
This file is a short pointer.

## Reading order

1. **`AI.md`** — index, key contracts, hard prohibitions.
2. **`AI_ARCHITECTURE.md`** — layers, modules, folder map.
3. **`AI_DATA_FLOWS.md`** — data scenarios.
4. **`AI_AJAX_REFERENCE.md`** — AJAX actions.
5. **`AI_TEMPLATE_MAP.md`** — theme templates.
6. **`AI_EXTENDING.md`** — how to add features.

> If the task is an edit in a specific area, read only `AI.md` + the relevant
> specialized file.

## Short rules

- **jQuery is prohibited on the frontend.** Only Vanilla JS and ES modules.
- **Do not change DOM selectors and AJAX actions** without synchronously editing PHP and JS.
- **Do not change the meta field and cookie format** — there is already populated data.
- **Layer separation:** logic in the plugin, markup in the theme.

---

> ⚠️ **For the `historic/php56-jquery` branch:** the "jQuery is prohibited" rule **does not apply**.
> The historic version is built on jQuery 2.2 and PHP 5.6. The current rules
> from this file apply to the modern version of the plugin. See `README.md`
> and `AI_CONTEXT.md` for details on the historic branch.

---

## Origin of this documentation

The AI documentation set (`AI.md`, `AI_ARCHITECTURE.md`, `AI_DATA_FLOWS.md`,
`AI_AJAX_REFERENCE.md`, `AI_TEMPLATE_MAP.md`, `AI_EXTENDING.md`, and this file)
was **written by [DeepSeek](https://www.deepseek.com/)** in response to prompts
and requirements provided by the project maintainer (Ivan Voitkov).

In other words, the **content, structure, and wording** of these files were
produced by the model, while the **scope, goals, and acceptance criteria** were
defined by the developer. Every contract, prohibition, and architectural
statement in these files reflects decisions made by the maintainer and the
actual state of the repository — DeepSeek only formalized them into the
documentation format you see here.

If you (an AI agent) rely on these files, treat them as a **secondary source**:
the primary source of truth is always the code itself.
