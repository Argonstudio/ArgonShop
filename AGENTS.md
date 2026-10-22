# AGENTS.md

> Full project context for AI assistants is in [`AI.md`](AI.md) and its related documents.

## Why this file

Different AI assistants look for different context file names:

| Tool | What it reads automatically | Status in this repository |
|------------|---------------|---------------|
| OpenAI Codex | `AGENTS.md` | ✅ this file |
| Cursor | `AGENTS.md`, `.cursor/rules/` (legacy: `.cursorrules`) | ✅ `AGENTS.md` |
| Claude Code | `CLAUDE.md` | ✅ `CLAUDE.md` — imports `AGENTS.md` and `AI.md`; `.claude/` — `/check`, `/new-ajax` commands and a static-check hook |
| Aider | nothing by default | ⚠️ run `aider --read AI.md` (or list it under `read:` in `.aider.conf.yml`) |
| Windsurf, other tools | their own rules files | ⚠️ attach `AI.md` to the context manually |

No tool reads `AI.md` by itself — it is the shared index that the pointers lead to.
To avoid duplication, **all context lives in `AI.md` and `docs/ai/AI_*.md`**.
This file is a short pointer.

## Reading order

1. **`AI.md`** — index, key contracts, hard prohibitions.
2. **`docs/ai/AI_ARCHITECTURE.md`** — layers, modules, folder map.
3. **`docs/ai/AI_DATA_FLOWS.md`** — data scenarios.
4. **`docs/ai/AI_AJAX_REFERENCE.md`** — AJAX actions.
5. **`docs/ai/AI_TEMPLATE_MAP.md`** — theme templates.
6. **`docs/ai/AI_EXTENDING.md`** — how to add features (including multi-region / cities, section 11).
7. **`docs/ai/AI_DESIGN_RATIONALE.md`** — architectural intent; read before major refactors or adding infrastructure.
8. **`docs/ai/AI_FUNCTION_NAMES.md`** — old → new function names (1.1.0).
9. **`CHANGELOG.md`** — what changed between versions.
10. **`docs/ai/AI_TESTING.md`**, **`docs/ai/AI_VERSIONING.md`**, **`docs/ai/AI_I18N.md`** — checks, versions, translations.

> If the task is an edit in a specific area, read only `AI.md` + the relevant
> specialized file.

## Short rules

- **jQuery is prohibited on the frontend.** Only Vanilla JS and ES modules.
- **Do not change DOM selectors and AJAX actions** without synchronously editing PHP and JS.
- **Do not change the meta field and cookie format** — there is already populated data.
- **Layer separation:** logic in the plugin, markup in the theme.
- **Prices only from the DB** (`as_get_product_price()`), required fields only on the server (`as_get_required_fields()`), nonce in every AJAX action.
- **New functions get the `as_` prefix** (a few older functions use `argon_shop_` / `argon_search_` / `ars_` — do not copy that); renaming a public function requires a wrapper in `includes/legacy-functions.php`.
- **All user-facing texts in English inside `__()` / `esc_html_e()`** with domain `argon-shop` (plugin) or `argon-shop-theme` (theme); update translations (`AI_I18N.md`).
- **JS text only via `t()`** from `core/config.js`; **prices only via the currency helpers** (`as_price_html()`, `astheme_price_html()`); **code comments and docs in English**.
- **PHP / WordPress:** minimum PHP 8.3 / WordPress 7.1 (`Requires PHP` / `Requires at least` headers). Do not add workarounds for older versions; do not use features newer than PHP 8.3 (e.g. PHP 8.4 property hooks) without raising the minimum.
- **Cities / multi-region:** ask the user about WordPress Multisite first (`docs/ai/AI_EXTENDING.md`, section 11); the city functions described there do not exist yet.
- **Versions:** never add `@version` to files; each edit → line in `CHANGELOG.md` `[Unreleased]`; bump the version only on release (`AI_VERSIONING.md`).
- **Check your change:** `php tests/static-check.php` always, `wp eval-file tests/smoke-test.php` on a test site. No terminal — tell the user what to run and what to look at (`AI_TESTING.md`). Never weaken a test to make it pass.

---

> ⚠️ **For the `historic/php56-jquery` branch:** the "jQuery is prohibited" rule **does not apply**.
> The historic version is built on jQuery 2.2 and PHP 5.6. The current rules
> from this file apply to the modern version of the plugin. See `README.md`
> and the historic branch itself for details.

---

## Origin of this documentation

The AI documentation set (`AI.md`, this file, `tests/README.md` and every file
in `docs/ai/`) was worked on by:

- **Ivan Voitkov** — project maintainer: scope, goals, contracts, prohibitions,
  acceptance criteria and final review;
- **[DeepSeek](https://www.deepseek.com/)** — initial structure and wording of
  the documentation set;
- **[Claude](https://www.anthropic.com/claude)** (Anthropic) — audit of the
  documentation against the code, corrections, multi-region (cities) section.
- **ChatGPT (OpenAI)** — architectural rationale in
  `docs/ai/AI_DESIGN_RATIONALE.md` and the related index/documentation update.

Every contract, prohibition, and architectural statement reflects decisions made
by the maintainer and the actual state of the repository.

If you (an AI agent) rely on these files, treat them as a **secondary source**:
the primary source of truth is always the code itself.
