# AI Entry Point

This is the execution contract for AI working on projects derived from this starter kit. Do not read all of `docs/` for every task; read this file, `project-context.md` when needed, and only the rules routed to the task.

This repository is core source only, not a runnable Laravel application. Run Composer, Artisan, Pint, Pest, migrations, and setup commands from the Laravel host that contains this repository as the `starterkit-larawire-private/` Git submodule. The installer maintains a connector in the host `AGENTS.md`; `docs/...` in this contract means `starterkit-larawire-private/docs/...`, while `app/`, `routes/apps/`, `resources/views/apps/`, `database/migrations/apps/`, `tests/`, and `issues/` belong to the host.

## Required context

- This is a private/internal-company application, not a SaaS product.
- Use the versions locked by the host dependencies. Inspect the active source before assuming a brand, framework/package API, theme, or icon set.
- Access is module-based: a role can own many modules; routes and menus follow modules. Superuser is a hidden system account and non-superusers cannot view, modify, or delete it.
- App/module/route/menu metadata is code-first and synchronized to the database. UI is Indonesian except familiar terms such as username, password, email, role, and module.
- Do not enable config cache in development; use it only for production deployment.
- The core repository may contain only starter source, core migrations/routes/views/assets, documentation/rules, and `installer/`; never add a Laravel shell, sample app, development database, project landing, or host feature issue.

Read [project context](docs/rules/project-context.md) once when unfamiliar with the project or after context loss.

## Default implementation contract

- The developer supplies business need, flow, data, and relevant roles; do not ask them to repeat starter technical standards.
- In a derived Laravel host, before changing project feature behavior or fixing a project bug, require: chat confirmation, a detailed specification in `issues/`, then explicit approval of that file. Use `issues/feature_<name>_<YYYY_MM_DD_HHMMSS>.md` or `issues/bug_<name>_<YYYY_MM_DD_HHMMSS>.md`; archive only completed, verified work as `issues/archives/done_<original-name>.md`. This gate does not apply to direct maintenance of this canonical starter repository: execute explicitly requested core changes directly, with proportionate verification.
- Do not enter the specification gate for a new module until its owning App, module name, and menu shape (single or parent/children) are known. Follow `feature-development.md`.
- Apply authorization, validation, injection/mass-assignment protection, server-side pagination, efficient queries, audit logging, transactions, locale/formatting, Livewire/Alpine patterns, UI state, production-safe migrations, and relevant tests by default.
- Mutable business-entity modules provide create, update, archive/soft delete, restore, permanent delete, selection, bulk actions, and by-filter actions unless the relevant owner rule documents and tests an exception (derived, append-only, audit/compliance, pivot, or system metadata data).
- **Every tabular data presentation in a Livewire project must use `power-components/livewire-powergrid` with the active-theme adapter.** Data source, search, enabled column filters, sorting, and pagination are server-side.
- **Column filters are enabled by default for every meaningful data column** and must match its type: text search for text, select/multi-select or boolean control for enumerations/booleans, and inclusive date-from/date-to ranges for dates/datetimes. They update live (debounced where querying), never behind an Apply/Search button, and their state—including pagination reset behavior—survives a page reload via the supported PowerGrid/Livewire URL/session mechanism.
- A single card-form filter row above the grid is optional and may contain only cross-column, composite, high-value, or otherwise non-redundant controls. Do not duplicate a column filter there. Keep column widths proportional to both filter controls and displayed values; maintain readability and responsive behavior, and use horizontal table scrolling when necessary.
- App features follow `Apps/<Subdomain>` ownership. Read `architecture.md` before creating files. App migrations live in `database/migrations/apps/<subdomain>/`; App APIs live in `routes/apps/<subdomain>.api.php` at `api.<APP_DOMAIN>/<subdomain>` with no `/api` prefix and only when `STARTER_API_ENABLED=true`.
- Keep each complete upstream vendor template—its `assets/` and HTML together—at `docs/template/<theme>/` so it can be pasted there intact. Then generate the runtime starter Blade at `resources/themes/<theme>/views/starter/`, copy required runtime assets to `public/themes/<theme>/assets/`, register theme paths/PowerGrid adapter, and verify publishing. Keep page CSS/JS beside its owning Blade view. Prefer Alpine for small presentation state, Livewire only for server work, and deferred model binding for normal forms.
- Cross-theme parity is limited to capabilities, data, authorization, states, accessibility, and responsive behavior. Markup hierarchy, component selection, class names, spacing, typography, colors, icons, tables, forms, buttons, dropdowns, cards, tabs, alerts, and modals belong to the active vendor theme. Start from the closest example in that theme's atlas and HTML; never copy Tabler presentation into Dashcode, copy Dashcode presentation into Tabler, or invent one generic cross-theme appearance.
- Browser-native dialogs (`alert`, `confirm`, `prompt`, and equivalents) are forbidden. Replace every dialog with an active-template, theme-consistent modal. Use it for every user-action confirmation and compact, single-purpose mini form; complex, multi-step, or page-defining forms remain on a dedicated page. Follow `ui-ux.md` for modal validation, loading, and destructive-action behavior.
- Skip irrelevant standards instead of adding ceremonial code. Explain risk and obtain explicit approval for a requested deviation.

## Evidence and change discipline

- Trace existing flow before planning or changing code: route/menu → Livewire/controller → service → interface/repository → model/migration → related test/config.
- Call something existing only when source, schema, config, tests, or command output proves it. Separate confirmed requirements, findings, proposals, and open questions.
- Stop and ask for a material business/authorization/data decision that cannot be discovered. Reuse the existing source of truth and closest sibling; do not add a layer, package, config, service, daemon, web-server configuration, or abstraction without verified need and approval.
- Verify installed versions from host lock/config files. Make the smallest root-cause change, preserve unrelated worktree changes, and update an affected rule/context when an approved exception changes a core standard.

## Rule router

| Task | Read |
|---|---|
| App feature | `feature-development.md` |
| New App/subdomain | `app-subdomain.md` |
| Route/menu/module/role | `access-control.md` |
| Model/mutation/transaction | `audit-logging.md` |
| Livewire/form/table/modal/loader | `ui-ux.md` |
| Theme baru/audit atau adapter theme | `theme-integration.md`, `ui-ux.md` |
| Config/upload/login/lock screen | `security-and-config.md` |
| PHP/Laravel conventions | `code-style.md` |
| Query/pagination/cache/bulk/assets | `performance.md` |
| Locale/number/date/currency | `localization-and-formatting.md` |
| Testing/definition of done | `testing.md` |
| Shared hosting/deployment | `deployment.md` |
| Layer ownership/source of truth | `architecture.md` |
| Starter install/update/theme/extension | `README.md` |
| Canonical starter core change | `core-maintenance.md` |

Start with the minimum rules below, then add every owner rule touched by the change.

| Task type | Minimum |
|---|---|
| Small known bug/refactor | `feature-development.md`, closest sibling/test, owner rule |
| Existing-App CRUD | `feature-development.md`, `architecture.md`, `audit-logging.md`, `testing.md` |
| UI/interaction | `ui-ux.md`, `testing.md`, template atlas search |
| Theme baru/audit theme | `theme-integration.md`, `ui-ux.md`, `testing.md`, runtime map |
| Schema/data change | `feature-development.md`, `code-style.md`, `testing.md` |
| New App/subdomain | `app-subdomain.md`, `architecture.md`, `access-control.md`, `testing.md` |
| Configuration/auth/security/deployment | `security-and-config.md`, `deployment.md`, `testing.md` |
| Canonical starter maintenance | `core-maintenance.md`, owner rule, relevant host verification |

## Priority and efficient workflow

Priority: (1) platform/developer instructions and current explicit user decisions; (2) confirmed business, authorization, security, and data-integrity requirements; (3) the most specific architecture/owner rule; (4) proven source of truth; (5) conventions and UI examples. Never use a lower level to override a higher one; report a same-level conflict and request a decision.

1. Locate files with `rg` and read the nearest sibling before editing.
2. For derived-host implementation requests, do read-only discovery for the standard chat confirmation; after approval, write one detailed issue specification. Skip this step for canonical starter maintenance and follow `core-maintenance.md`.
3. For UI, search `docs/template/<theme>/template.md`, then open only one to three relevant HTML sources.
4. Do not repeat general rules in feature documents or create issues for read-only diagnosis, status reports, or documentation-only work.

## Execution, installation, and verification

- In a host project, `starterkit-larawire-private/` is a Git submodule. Project features treat it as read-only. Universal improvements are made and verified on the submodule's `master` branch, committed and pushed to the starter remote, then the host commits and pushes the updated submodule gitlink.
- Ignore `installer/` after setup unless the task concerns the installer. Install with `php starterkit-larawire-private/installer/install.php`. It detects the host structure: a fresh Laravel host receives a destructive-database notice and continues without reset confirmation; a non-fresh host lists the findings and requires explicit `y` before any mutation. Both normal paths run `migrate:fresh`; never use installation for routine updates or deployment.
- `--reset` is local/development only; require `y`, then `RESET`, run `migrate:fresh`, preserve project source and uploads, and reject production before mutation.
- First deployment: `composer install`, then `starter:setup`. Updates: `composer install`, then `starter:sync`. Neither may reset existing credentials; only create the app key when empty.
- The installer manages only its marked connector block in host `AGENTS.md`; preserve every project instruction outside it. Do not ask developers to copy core source or edit individual connectors.
- For derived-host feature work, after specification approval, wait for explicit execution approval. Re-read the approved issue before execution; return to confirmation/specification if a new instruction materially changes scope. Canonical starter maintenance follows the direct-execution rule above.
- Documentation-only/typo work may proceed directly if it does not change business flow, authorization, data, API, or deployment. For code work, run `php artisan make:* --no-interaction` from the host, create production-safe migrations, update relevant tests, run Pint after core PHP changes, then focused integration tests followed by the relevant suite. Never delete tests without approval.

## Rule evolution

Before code execution, assess whether a new instruction is reusable. Request short confirmation before making it a starter/project standard; an explicit developer request to add or change a canonical starter rule already counts as that confirmation and must not trigger a duplicate gate or planning issue. Then update the most specific owner rule in the same change. Do not globalize one-off business decisions, secrets, temporary workarounds, or feature details. Keep rules general, concise, executable, and non-duplicative; explain and get approval before replacing a conflicting rule.
