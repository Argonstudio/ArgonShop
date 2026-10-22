# AI_DESIGN_RATIONALE.md — rationale behind architectural decisions

> Document for AI assistants that analyze, extend, or refactor ArgonShop in the `main` branch.
>
> This is not marketing copy and does not replace the technical documentation. Its purpose is to explain why the project is designed this way and help agents avoid unnecessary complexity.
>
> **The current code is the source of truth.** If the code conflicts with this document or specialized documentation, report the discrepancy to the user. Do not assume behavior that is not confirmed by the code.

## 1. Primary design goal

ArgonShop is intended for custom online stores where a developer and AI assistants adapt the code to the requirements of a particular business.

The architecture aims for four properties:

1. **Understandability:** a developer or agent can find the relevant logic and trace its calls without studying layers of indirect abstractions.
2. **Explicit boundaries:** business logic, presentation, input data, and external contracts have clear locations.
3. **Localized changes:** a typical enhancement can be made in a limited set of files without rewriting the entire system.
4. **Verifiability:** changes can be checked with the existing static checks, smoke tests, and manual checklist.

The goal is neither the fewest possible lines of code at any cost nor rejection of modern tools. The goal is to choose the simplest solution that reliably solves the task and fits the existing architecture.

## 2. Procedural PHP is a deliberate practical choice

ArgonShop PHP code is primarily organized around `as_`-prefixed functions, standard WordPress APIs, and explicit file inclusion. Many operations can therefore be located by searching for a function name, and execution flow can be traced without necessarily studying chains of classes, factories, interfaces, and dependency containers.

For AI-assisted work, this may reduce the effort required to understand a specific task:

- a function can usually be found by name and read at its definition;
- calls and dependencies can be investigated with ordinary repository searches;
- WordPress integration relies on familiar functions and hooks;
- fewer project-specific abstraction layers mean fewer conventions that must be reconstructed from documentation.

This is **not a claim that procedural PHP is always better than OOP**. AI can work with classes, namespaces, and Composer as well. An advantage exists only when the particular structure reduces the complexity of understanding and changing the code.

### Rules for agents

- Do not rewrite existing functions as classes merely for the sake of a “modern style.”
- Do not add namespaces, a dependency container, factories, interfaces, or Composer without a specific technical reason.
- Do not treat classes as a forbidden technology: they are appropriate when a module genuinely needs managed state, multiple implementations of a shared contract, a complex lifecycle, or integration isolation.
- Name new global PHP functions with the `as_` prefix; do not rename existing public functions without a compatibility wrapper and corresponding documentation updates.
- Preserve the established style of a module unless changing it is part of the task.
- Do not add `declare(strict_types=1)` indiscriminately to all existing files without analyzing compatibility and practical benefit. Add parameter and return types deliberately, and check their impact on existing callers.

## 3. Use WordPress as a platform instead of rebuilding it

ArgonShop uses standard WordPress mechanisms where they fit the task: hooks, post types, taxonomies, metadata, user APIs, and other built-in facilities.

Prefer the WordPress API over creating a parallel mechanism that duplicates functionality already available. A custom abstraction is justified when it solves a concrete project problem, not merely when it makes the code resemble an architectural pattern from another framework.

Do not assume that using WordPress automatically guarantees security, performance, or compatibility. Validate input, permissions, queries, and behavior on the target platform version.

## 4. Separate business logic from presentation

In the current architecture:

- the **plugin** is responsible for commerce logic, data, CPTs and taxonomies, server-side processing, AJAX, and related contracts;
- the **theme** is responsible for page HTML, interface structure, and styles;
- functional CSS in the plugin is permitted where it is needed for the initial state of JavaScript-controlled elements — see `AI.md`.

This separation makes it possible to create different interfaces and themes while retaining the same commerce foundation. Do not move business logic into the theme merely because a change is visible on a page. Do not put design-specific markup into the plugin without a compelling reason.

If a task crosses the boundary between layers, first determine where the responsibility belongs, then update all affected components and the relevant documentation together.

## 5. JavaScript: native modules and minimal build infrastructure

The ArgonShop frontend uses Vanilla JavaScript, native ES modules, the Fetch API, and the DOM API. jQuery is prohibited on the frontend; admin-area exceptions are described in `AI.md` and the related documentation.

This arrangement avoids a mandatory build step and an additional dependency on a frontend framework. Direct imports, actual module files, and the ability to inspect behavior without reconstructing a hidden compilation process can help an agent.

This does not mean that native JavaScript is automatically better than every framework or always faster. It is the project’s current contract. Do not add a framework, bundler, or third-party library without a justified need and an assessment of its maintenance cost.

When changing the interface, preserve existing DOM contracts, use the current initialization and localization APIs, and update data-flow and template maps when affected.

## 6. Documents record contracts; they do not replace the code

ArgonShop has separate documents with distinct purposes:

- `AI.md` — entry point, key contracts, and prohibitions;
- `AI_ARCHITECTURE.md` — project structure and module locations;
- `AI_DATA_FLOWS.md` — data flow through key scenarios;
- `AI_AJAX_REFERENCE.md` — AJAX contracts;
- `AI_TEMPLATE_MAP.md` — template-to-function relationships;
- `AI_EXTENDING.md` — extension procedures;
- `AI_FUNCTION_NAMES.md` — public function-name compatibility map;
- `AI_TESTING.md` — checks and how to run them;
- `AI_VERSIONING.md` — release numbering and migrations;
- `AI_I18N.md` — localization and translation workflow;
- `tests/README.md` — test commands and requirements.

Read documentation relevant to the task rather than loading the entire set unnecessarily. This reduces irrelevant context, but does not replace checking the actual files, called functions, and current code.

When a contract changes, update the corresponding document in the same change. Do not copy large sections of specialized documentation into this file: this document explains the rationale and decision rules, while implementation details remain in their dedicated documents.

## 7. Simplicity does not replace testing

An understandable architecture reduces analysis effort, but does not guarantee that a change is correct or safe. AI-generated code can contain logic errors, vulnerabilities, incompatible contracts, and regressions.

For every task:

1. Identify affected contracts and adjacent scenarios.
2. Before making changes, study the relevant documentation and existing implementation.
3. Make the smallest coherent change; do not mix it with unrelated refactoring.
4. Run `php tests/static-check.php`.
5. If a test WordPress installation is available, run `wp eval-file tests/smoke-test.php` and the relevant manual checks described in `AI_TESTING.md`.
6. Tell the user which checks were actually run. If tests were not run, state that explicitly.
7. Do not weaken tests merely to obtain a passing result.

The existing checks are useful, but they do not guarantee complete coverage of all browsers, hosting environments, payment integrations, load conditions, or security scenarios.

## 8. How to make architectural decisions

Before a major refactor, adding new infrastructure, or changing module boundaries, the agent should answer these questions:

1. What specific problem does the change solve?
2. Where is it evidenced by the current code, a test, or the user’s requirements?
3. Can it be solved with a smaller change within the existing architecture?
4. Which contracts, data, and user scenarios are affected?
5. How will the result be verified, and how can the change be rolled back?
6. Which documents and `CHANGELOG.md` entries must be updated?

Do not perform a large-scale architectural rewrite merely because an alternative approach is more fashionable, more familiar to the model, or common in another project. Compare options using concrete criteria: understandability, correctness, security, maintenance cost, and compatibility.

If the existing architecture genuinely prevents a requirement from being met, do not defend it automatically. Explain the problem, present options and consequences. If the choice affects product decisions or has significant irreversible consequences, obtain the user’s agreement before making a major change.

## 9. Limits of AI-friendly claims

AI-friendly means that the project aims to make changes easier for agents to understand and verify. It does not mean that:

- every model will understand the entire repository from a single link;
- the entire codebase will always fit into every model’s context window;
- an agent without a running WordPress installation can verify every runtime scenario;
- documentation alone guarantees security;
- ArgonShop functionally replaces WooCommerce or another mature commerce platform in every scenario.

When evaluating an advantage, state exactly what was tested. Claims about speed, catalog scale, security, and reliability must be supported by relevant measurements or tests, not inferred solely from code compactness.

## 10. Related documents

- [AI.md](../../AI.md) — main index and mandatory contracts.
- [AI_ARCHITECTURE.md](AI_ARCHITECTURE.md) — layers, modules, and file map.
- [AI_DATA_FLOWS.md](AI_DATA_FLOWS.md) — key data flows.
- [AI_EXTENDING.md](AI_EXTENDING.md) — practical instructions for adding functionality.
- [AI_TESTING.md](AI_TESTING.md) — checks.
- [POSITIONING.md](../../POSITIONING.md) — ArgonShop’s purpose and platform selection criteria.

## 11. Authorship and AI contribution

This architectural rationale was prepared collaboratively by the ArgonShop maintainer and **ChatGPT (OpenAI)**. ChatGPT contributed to the analysis, structure, drafting, and refinement of this document. The project-specific decisions and implementation facts remain attributable to the maintainer and the repository; the maintainer is responsible for final review and approval.

