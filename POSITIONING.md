# POSITIONING.md — when to use ArgonShop, and when not to

This document explains the niche ArgonShop is designed for. It is intended
for developers, clients, and AI assistants evaluating whether this stack
fits a particular project.

> **TL;DR:** ArgonShop is the right choice for **custom, non-standard
> e-commerce projects** where uniqueness and full code ownership matter.
> For **typical, off-the-shelf shops**, WooCommerce or a similar platform
> will usually be a better fit.

---

## 1. What ArgonShop is

ArgonShop is a **lightweight, self-contained e-commerce stack** for WordPress:
a plugin (business logic) plus a theme (markup and styles). It is a monorepo,
explicitly designed to be **readable, extensible, and AI-friendly**.

It is not a WooCommerce competitor by feature count. It is a **foundation
for custom shops** where WooCommerce would get in the way.

---

## 2. When ArgonShop is the right choice

Choose ArgonShop if most of the following are true:

- **The shop is non-standard.** Unusual pricing logic, B2B tiers, custom
  catalog structure, niche product types, or business rules that don't map
  cleanly onto WooCommerce.
- **Uniqueness beats features.** The client pays for a shop that does
  something others don't — not for a copy of a template.
- **Full code ownership matters.** You want to own the stack, not rent it
  from a vendor whose updates can break your customizations.
- **Long-term support is expected.** A custom codebase is easier to maintain
  over years than a heavily-customized platform you don't control.
- **The team is small.** One developer plus AI assistants can carry the
  whole project. No need to hire a "WooCommerce senior" to make changes.
- **Hosting and updates are under your control.** No third-party update
  cycles dictating your roadmap.
- **AI-assisted development is part of the workflow.** ArgonShop is designed
  for this from the ground up (see section 4).

**Typical examples:**

- B2B shops with wholesale price steps.
- Niche catalogs with non-trivial filtering or cross-linking.
- Shops where checkout flow must follow a specific business process.
- Portfolio and demo projects that need to demonstrate a clean architecture.
- Client projects where the shop is one part of a larger custom site.

---

## 3. When WooCommerce (or another platform) is a better fit

Be honest with yourself. Choose a mature platform if most of the following
are true:

- **The shop is typical.** Catalog, cart, checkout, payment, shipping —
  nothing exotic.
- **Speed to launch matters more than uniqueness.** Two weeks, not two months.
- **The client will manage it themselves.** They need a familiar admin UI
  and expect "the WordPress way".
- **Integration with specific services is required.** If a ready-made plugin
  exists for a payment gateway, shipping provider, or tax service, writing
  your own integration is wasted effort.
- **The project will be handed off** to another team who will expect a
  well-known platform.
- **Edge cases matter and you can't afford to discover them yourself.**
  WooCommerce has been battle-tested on millions of shops. Those closed
  race conditions, currency rounding rules, and checkout corner cases are
  expensive to reproduce from scratch.

**Typical examples:**

- A standard online store for a small business.
- A shop where the client wants "WooCommerce but with a custom theme".
- Projects that need Stripe/PayPal/10 other gateways out of the box.

---

## 4. Why ArgonShop works well with AI assistants

This is a deliberate design goal, not an accident. The project has properties
that most platforms — including WooCommerce — do not and cannot have:

| Property | Why it matters for AI |
|---|---|
| **Small codebase** | The entire project fits in an LLM context window. The AI sees the whole picture. |
| **Explicit contracts** | DOM selectors, AJAX actions, meta keys, and cookie formats are documented. The AI does not guess. |
| **Hard prohibitions** | "Do not do X" rules work better as prompts than "do Y" descriptions. |
| **Layered architecture** | The AI knows where new code belongs: logic in the plugin, markup in the theme. |
| **Vanilla JS, no framework** | No hidden magic, no build step to explain, no framework version drift. |
| **No bundler** | ES modules load directly. Nothing to compile, nothing to misconfigure. |
| **Dedicated AI docs** | `AI.md` and related files give assistants reliable context from the first request. |

Compare this with WooCommerce:

- **Huge codebase** — the AI can only see a fragment at a time.
- **Implicit conventions** — thousands of hooks, filters, and side effects
  the model has to reconstruct from partial knowledge.
- **Framework lock-in** — jQuery, Backbone, React admin, REST API — many
  layers to reason about at once.
- **Frequent breaking changes** — advice the AI gives today may be wrong
  after the next major release.

The conclusion is simple: **the more unique the shop, the bigger the
advantage of a small, AI-friendly stack over a large, generic platform.**

---

## 5. What ArgonShop is not

To set expectations correctly:

- **Not a drop-in replacement for WooCommerce.** Different scope,
  different goals.
- **Not a marketplace platform.** No multi-vendor logic out of the box.
- **Not a SaaS.** Self-hosted only.
- **Not feature-complete.** Payments, shipping, taxes, and reporting are
  built per-project, not shipped by default.
- **Not for "I want it to look like WooCommerce".** If that is the goal,
  use WooCommerce.

---

## 6. Decision checklist

Answer these questions. More "yes" answers point to ArgonShop; more "no"
answers point to WooCommerce or another platform.

1. Does the shop have non-standard business logic?
2. Is the client paying for uniqueness rather than a template?
3. Do you control the hosting and update cycle?
4. Will the project be maintained long-term by a small team?
5. Is AI-assisted development part of your workflow?
6. Are you comfortable building integrations yourself (payments, shipping)?
7. Is a custom admin UI acceptable (or even desired)?
8. Is the codebase expected to stay under your ownership?

**8 "yes"** → ArgonShop is almost certainly the right choice.
**4–7 "yes"** → ArgonShop can work, but budget extra time for integrations.
**0–3 "yes"** → Use WooCommerce, Shopify, or another mature platform.

---

## 7. Summary

> ArgonShop + AI = **fast development of unique shops** with full control.
> WooCommerce + AI = **fast launch of typical shops** with constraints.

These two approaches do not compete head-to-head. They serve different
segments. ArgonShop is at its strongest where WooCommerce is at its
weakest: B2B, non-standard catalogs, niche logic, and projects with a
long support horizon.

---

*This document is part of the ArgonShop AI documentation set.
See [AI.md](AI.md) for the full index.*
