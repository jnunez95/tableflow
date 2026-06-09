# TableFlow

A multi-tenant restaurant management platform built with Laravel 13, Inertia.js, Vue 3, Filament, and stancl/tenancy.

## Stack

- **Laravel 13** — Backend framework
- **Jetstream + Inertia + Vue 3** — Frontend SPA stack
- **Filament v4** — Admin panel (basic installation)
- **stancl/tenancy** — Multi-tenant architecture with separate databases per restaurant
- **Tailwind CSS** — Design system from `design/landing`

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- Herd (recommended) or Valet for local subdomain support

## Local Setup

```bash
# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# MySQL (central database)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS tableflow_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Database & demo tenant
php artisan migrate:fresh --seed

# Frontend assets
npm run dev

# Link with Herd
herd link tableflow
```

## URLs

| URL | Description |
|-----|-------------|
| `http://tableflow.test` | Central platform landing |
| `http://demo.tableflow.test` | Demo restaurant welcome (QR dining UI) |
| `http://tableflow.test/admin` | Filament admin panel |
| `http://tableflow.test/login` | Platform authentication |

## Demo Credentials

- **Email:** admin@tableflow.test
- **Password:** password

## Multi-Tenancy

- **Identification:** Subdomains (`demo.tableflow.test`)
- **Database strategy:** Separate database per tenant (`tableflow_tenant_{id}`)
- **Central DB:** Tenants, domains, platform users
- **Tenant DB:** Restaurant-specific data (users, teams, etc.)

### Create a Tenant

```bash
php artisan tinker
>>> $tenant = App\Models\Tenant::create(['id' => 'myrestaurant', 'name' => 'My Restaurant']);
>>> $tenant->domains()->create(['domain' => 'myrestaurant']);
```

Access at: `http://myrestaurant.tableflow.test`

## Design System

The welcome view implements the **Epicurean Modern** design system from [`design/landing/DESIGN.md`](design/landing/DESIGN.md):

- Deep Navy `#1E2E42` / Terracotta `#D35400`
- Hanken Grotesk + Source Sans 3 typography
- 8px spacing grid

## Phase 1 Scope

- [x] Laravel 13 project setup
- [x] Multitenancy with subdomains
- [x] Filament admin (basic)
- [x] Welcome landing view (tenant-facing)
- [ ] Menu digital, orders, KDS (future phases)
