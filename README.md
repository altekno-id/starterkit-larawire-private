[English](README.md) | [Bahasa Indonesia](README.id.md)

# Starterkit Larawire Private

> Built with open-source work from
> [Laravel](https://laravel.com/),
> [Laravel Livewire](https://livewire.laravel.com/),
> [Laravel Lang](https://laravel-lang.com/),
> [Livewire PowerGrid](https://livewire-powergrid.com/),
> [Tabler](https://tabler.io/admin-template),
> [Dashcode](https://codeshaper.net/), and
> [Scramble](https://scramble.dedoc.co/). Thank you to every author and
> contributor who builds and maintains these projects.

## A. About

### What is Starterkit Larawire Private?

Starterkit Larawire Private is a foundation for building private, internal
company applications with **Laravel** and **Livewire**. It plays a similar role
to Laravel's authentication starter kits, but includes more of the common
building blocks needed by an internal business application.

After installation, you already have:

- login with username or email, logout, lock screen, and account security;
- user, role, module, and dynamic access management;
- multiple business Apps and subdomains inside one Laravel project;
- code-first routes and menus that are synchronized to the database;
- company settings and an activity log;
- Tabler and Dashcode themes, each with vertical and horizontal layouts;
- server-side tables powered by Livewire PowerGrid;
- an optional API Gateway with Scramble documentation;
- installation, production setup, update, asset publishing, and migration
  commands; and
- AI-ready development rules for applications derived from this starterkit.

This repository is **not a standalone Laravel application**. It is installed as
a Git submodule named `starterkit-larawire-private` inside a Laravel project.
Your business features live in the Laravel project, while the reusable core
lives in this submodule.

This starterkit is designed for one company or client per installation. It is
not a SaaS multi-tenant system.

### Technologies used

| Technology | Purpose |
|---|---|
| Laravel | Application framework, routing, database, validation, and security. |
| Livewire | Interactive pages and forms without building a separate SPA. |
| Livewire PowerGrid | Server-side business tables, search, filters, sorting, and pagination. |
| Laravel Lang | Indonesian Laravel validation and framework translations. |
| Tabler | First visual theme, with vertical and horizontal navigation. |
| Dashcode | Second visual theme, with vertical and horizontal navigation. |
| Scramble | Optional API documentation and OpenAPI output. |
| Git submodule | Keeps the starterkit core versioned separately from the Laravel application. |

The active theme changes presentation only. Data, features, authorization, and
behavior stay the same, while each theme keeps its own native components and
visual style.

### The basic idea: App, subdomain, module, route, menu, and role

One installation still uses **one Laravel project, one database, and one login
system**. Apps divide that project into clear business areas.

| Term | Beginner-friendly meaning |
|---|---|
| **App** | A major business area, such as Sales or HR. |
| **Subdomain** | The address used to open an App, such as `sales.company.com`. |
| **Module** | A group of related features and the main access boundary. |
| **Route** | The URL and action that opens a page. |
| **Menu** | A navigation link that points to a route. |
| **Role** | A collection of modules that a user is allowed to access. |

The access flow is:

```text
User -> Role -> Allowed modules -> Allowed routes
```

Menus help users navigate, but menus do not provide security. Server-side route
middleware checks access using the module assigned to the user's role.

### Example: an internal ERP application

Imagine that you want to build an internal ERP for Sales, HR, Warehouse, and
Employees. You do not need four Laravel projects. You can keep everything in
one project and separate the large business areas into Apps:

```text
Internal ERP                                  One Laravel project and database
├── company.com                               Login and global management
├── sales.company.com                         Sales App
│   ├── Customer module
│   └── Sales Order module
├── hr.company.com                            HR App
│   ├── Employee module
│   └── Attendance module
└── warehouse.company.com                     Warehouse App
    ├── Stock module
    └── Stock Movement module
```

Employees are modules inside the HR App because they belong to the same
business area. If Employees later become a fully separate system area, you can
create an `employee` App at `employee.company.com`.

All users sign in through `company.com`. A Sales user can receive only the
Customer and Sales Order modules. An HR user can receive the Employee and
Attendance modules. A manager can receive modules from more than one App.

Every domain and subdomain points to the **same `public` directory** of the
same Laravel project.

### How an App works in the source code

Create a new App from the Laravel project root:

```bash
php artisan starter:make-app sales --name="Sales"
```

The command creates a working example and synchronizes it automatically. The
most important files are:

```text
config/apps/sales.php
routes/apps/sales.php
routes/apps/sales.api.php
app/Livewire/Apps/Sales/
resources/views/apps/sales/
database/migrations/apps/sales/
tests/Feature/Apps/Sales/
```

You normally develop an App in this order:

1. Define the App, modules, menus, icons, and default pages in
   `config/apps/sales.php`.
2. Define web routes in `routes/apps/sales.php` using the name pattern
   `<app>.<module>.<action>`.
3. Build Livewire classes in `app/Livewire/Apps/Sales/<Module>/`.
4. Build Blade views in `resources/views/apps/sales/<module>/`.
5. Put business migrations in `database/migrations/apps/sales/`.
6. Add tests in `tests/Feature/Apps/Sales/`.
7. Run `php artisan starter:sync` after changing App config, modules, menus, or
   routes.

For example:

```text
config module code: sales_order
route name:          sales.sales_order.index
```

The second route-name segment must match the module code. A menu entry in the
config points to that route name. During synchronization, the starterkit checks
these relationships and stores the App, module, route, and menu registry in the
database. Do not edit the starter registry tables manually.

### How dynamic role access works

Application structure is defined in code, but access is managed dynamically
from the **Settings -> Roles** page:

1. Create or edit a role.
2. Select the modules the role may access.
3. Select the first page for every App granted to that role.
4. Optionally grant global access to Settings or Activity Log.
5. Assign the role to a user from User Management.

No source-code change is needed when an administrator changes a user's role or
module access. The new access is read from the database. However, when a
developer adds or removes modules, routes, or menus in code, run
`starter:sync` first so Role Management receives the latest structure.

The built-in Superuser is a protected system account with full access. Other
users receive access only through their assigned role.

### Themes and navigation layouts

Choose the theme and layout in `.env`:

```env
STARTER_THEME=tabler
STARTER_LAYOUT=vertical
```

Available themes are `tabler` and `dashcode`. Both support `vertical` and
`horizontal` layouts. If configuration was previously cached, run:

```bash
php artisan optimize:clear
```

### Developing with an AI agent

The installer connects the Laravel project's `AGENTS.md` to the starterkit
rules. When requesting a business feature, tell the agent the App, module, menu
shape, business flow, and relevant roles. For example:

```text
Create a Sales Order module in the sales App. Its menu contains Pipeline and
Transactions. Sales users can manage records, while Managers can only view the
report.
```

The rules guide authorization, validation, audit logging, migrations, UI,
performance, and tests. These project-development rules do not prevent direct
maintenance of the starterkit core itself.

## B. Installation

All commands in this section are run from the **Laravel project root**, not
from inside the starterkit submodule.

### 1. First local installation

Prepare these items first:

1. a fresh Laravel project;
2. a new or empty database and its connection in `.env`;
3. the local root domain in `APP_URL`; and
4. Git and Composer.

Example `.env` values:

```env
APP_URL=http://company.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_local
DB_USERNAME=root
DB_PASSWORD=
```

Then install the submodule and run the installer:

```bash
git submodule add https://github.com/altekno-id/starterkit-larawire-private.git starterkit-larawire-private
php starterkit-larawire-private/installer/install.php --company="Company Name"
```

The installer adds the other starterkit environment variables automatically,
installs dependencies, prepares Laravel, runs migrations, publishes assets,
creates the company, and creates the Superuser account.

During installation, you may enter the first App code, such as `sales`, or
leave it empty and create Apps later with `starter:make-app`. If you create an
App, make sure its local subdomain resolves to the same Laravel `public`
directory as `APP_URL`.

> **Database warning:** first installation runs `migrate:fresh`, which deletes
> every table and row in the connected database. A fresh Laravel structure
> continues after showing the warning. If application code already exists, the
> installer lists its findings and asks for confirmation before changing
> anything.

The login form accepts the Superuser username or email configured in `.env`.
The generated development password is only for local use; set a strong
`STARTER_SUPERUSER_PASSWORD` before production.

### 2. Reinstall locally

Choose the case that matches your goal.

#### Use a new or empty database with the same installed project

Change the database connection in `.env`, then run:

```bash
php artisan starter:setup --company="Company Name"
```

This is the efficient option after switching from SQLite to MySQL or moving to
another empty database. It runs migrations, creates the company and Superuser,
then synchronizes the existing Apps. Do not run the installer again for this
case.

#### Completely reset the current local database

```bash
php starterkit-larawire-private/installer/install.php --reset --company="Company Name"
```

`--reset` is allowed only when `APP_ENV` is `local` or `development`. It asks
for two confirmations, runs `migrate:fresh`, and rebuilds starter data while
preserving your source files and uploads. Never use it in production.

### 3. Synchronize local App structure

After changing `config/apps/*.php`, `routes/apps/*.php`, modules, menus, or
landing pages, run:

```bash
php artisan starter:sync
```

This command applies pending migrations, publishes required assets, validates
the App structure, and synchronizes App, module, route, and menu metadata. It
does not reset the database or replace an existing Superuser password.

The `starter:make-app` command already synchronizes the new App automatically.

### 4. Update the starterkit locally

From the Laravel project root:

```bash
git submodule update --init --remote starterkit-larawire-private
composer install
php artisan starter:sync
```

Your Laravel repository will show the updated submodule pointer as a Git
change. Review it, then commit and push it as part of the Laravel project so
other environments use the same starterkit version.

### 5. Quick production deployment

The root domain, every App subdomain, and the optional API subdomain must point
to the same Laravel `public` directory:

| Address | Document root |
|---|---|
| `company.com` | `/home/user/erp/public` |
| `sales.company.com` | `/home/user/erp/public` |
| `hr.company.com` | `/home/user/erp/public` |
| `api.company.com` (optional) | `/home/user/erp/public` |

Set production database values and at least these environment values before
setup:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://company.com
APP_DOMAIN=company.com
SESSION_DOMAIN=.company.com
SESSION_SECURE_COOKIE=true
STARTER_SUPERUSER_PASSWORD=use-a-strong-password
```

Do not commit the production `.env` file.

#### First production deployment

```bash
git clone --recurse-submodules <laravel-repository> <project-folder>
cd <project-folder>
composer install --no-dev --optimize-autoloader
php artisan starter:setup --company="Company Name"
```

Use `starter:setup` when the production database is new or empty. If you
restored an existing starterkit database, run `php artisan starter:sync`
instead.

#### Routine production update

```bash
git pull --recurse-submodules
composer install --no-dev --optimize-autoloader
php artisan starter:sync
```

`starter:sync` handles pending migrations, asset publishing, App registry, the
storage link, and production cache. Separate `migrate`, `storage:link`, or
`optimize` commands are not needed.

Before updating production, make sure the worktree is clean and back up the
database and uploaded files when the release contains risky migrations.

## C. Optional API Gateway

Skip this section if the application does not need an API. The gateway is
disabled by default and registers no API endpoints or documentation while
disabled.

### Enable the gateway

Set this value in `.env`:

```env
STARTER_API_ENABLED=true
```

If configuration was cached, run `php artisan optimize:clear` locally or
`php artisan starter:sync` during deployment.

Each App owns its API routes in:

```text
routes/apps/<app>.api.php
```

For the Sales App, write paths relative to the App because the starterkit adds
the domain and App prefix automatically. This example assumes that Laravel
Sanctum has already been installed and configured by the application:

```php
Route::get('/orders', SalesOrderIndexController::class)
    ->middleware('auth:sanctum')
    ->name('orders.index');
```

The endpoint becomes:

```text
https://api.company.com/sales/orders
```

The shared gateway rules are:

- point `api.company.com` to the same Laravel `public` directory;
- keep API code in the owning App's `.api.php` file;
- choose and configure the API authentication method required by the
  application; the gateway does not issue API tokens by itself;
- give every business endpoint explicit authentication and authorization;
- use the built-in API middleware and rate limiting;
- do not add API routes to web menus or role-module synchronization;
- keep CORS closed unless a specific trusted frontend requires it; and
- never use wildcard origins for authenticated endpoints.

Scramble serves the API documentation at `https://api.company.com/` and the
OpenAPI document at `https://api.company.com/openapi.json`. In production, the
documentation is restricted to an authenticated Superuser.

## Important reminders

- One installation represents one company/client, not multiple SaaS tenants.
- All Apps share the same login, database, users, roles, and global settings.
- Modules grant access; menus provide navigation only.
- Business features belong to the Laravel project, not the starterkit
  submodule.
- Never commit `.env`, passwords, database credentials, tokens, or `vendor`.
