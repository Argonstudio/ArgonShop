---
description: Add a new AJAX action following the project rules (docs/ai/AI_EXTENDING.md, section 1)
argument-hint: <action_name> <what it does>
---

Task: add an AJAX action. Description from the user: $ARGUMENTS

Before writing code, read `docs/ai/AI_EXTENDING.md` (section 1 "New AJAX action" and section 13 "Pre-commit checklist") and `docs/ai/AI_AJAX_REFERENCE.md` (section "Conventions" — the rules for all handlers). If the action name or purpose is unclear, or it is needed both by guests and in the admin, ask the user first.

Steps:

1. **PHP handler** in a fitting file under `plugins/argon-shop/includes/interface/<feature>/` (or `includes/admin/…` for the admin):
   - `add_action( 'wp_ajax_<action>', … )`; `wp_ajax_nopriv_<action>` only if guests need it; never for admin actions;
   - a function with the `as_` prefix and `@since` of the next version (`docs/ai/AI_VERSIONING.md`);
   - first line — `check_ajax_referer( 'argon_shop_nonce', 'nonce' );` (order admin — `argon_shop_order_nonce`);
   - right after the nonce — a capability check that **stops** the request: `if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'Insufficient permissions', 'argon-shop' ) ); }` or `userID === get_current_user_id()`;
   - input via `absint()` / `sanitize_*()`; prices only from `as_get_product_price()`, never from the request;
   - response — `echo wp_json_encode( $data );` (or escaped HTML) and `wp_die();`; the project does not use `wp_send_json()`; no other output before the response;
   - texts in English inside `__()` with the `argon-shop` domain; comments in English.
2. **JS**: call via `arsApi.post` / `arsApi.postJson` in a module under `assets/js/interface/modules/`; no jQuery; user-facing text only via `t( 'key' )` — add the key to `I18N_DEFAULTS` (`core/config.js`) and to `argon_shop_js_i18n()`. A new module follows section 2 of `AI_EXTENDING.md` (import and `init()` in `main.js`).
3. **Translations**: update `.pot`, `ru_RU`, `fr_FR` (`docs/ai/AI_I18N.md`); no terminal — give the user an "English → Russian → French" table.
4. **Docs**: add the action to `docs/ai/AI_AJAX_REFERENCE.md` (file, function, nonce, JS, parameters, response, capability); new DOM selectors to `AI.md` (section 5); a `CHANGELOG.md` → `## [Unreleased]` → "Added" line. Do not bump the version. Tell the user the change also has to be ported to the Russian `ru` branch.
5. **Check**: `php tests/static-check.php` (the hook runs it after PHP edits); suggest a check in `tests/smoke-test.php` (`as_smoke_check()`) if the action changes data.

Finish with the checklist in section 13 of `AI_EXTENDING.md` and list the changed files.
