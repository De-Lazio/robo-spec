# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

RoboForge is a project-management platform for robotics teams (multi-step specs/CDC, technical resources, project teams, GitHub linking). Built with Laravel 12, Inertia 2, React and SQLite for local development. UI copy is French; code (classes, routes, tables, variables) is English — see "Code conventions" below.

The authoritative implementation plan, with full data model, phased rollout, and permission matrix, lives in `docs/PLAN_IMPLEMENTATION_ROBOFORGE.md`. Read it before working on anything beyond the existing Project domain — it defines the target architecture for Requirements/CDC, Resources, Members/Invitations, and GitHub integration, none of which exist yet.

## Commands

```bash
# Setup
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install

# Run locally (two terminals), or `composer run dev` to run server+queue+logs+vite together
php artisan serve
npm run dev

# Queue worker (needed once background jobs exist)
php artisan queue:listen

# Checks — run all three before considering backend/frontend work done
php artisan test
./vendor/bin/pint --test    # PHP style check; drop --test to auto-fix
npm run build                # also type-checks TSX via vite/tsc

# Single test
php artisan test --filter=ProjectServiceTest
php artisan test tests/Feature/Domain/Projects/ProjectPolicyTest.php
```

SQLite is the dev/test database (`database/database.sqlite` locally, `:memory:` under `phpunit.xml`). Migrations must stay portable to MySQL/PostgreSQL (explicit FKs, indexes, ULIDs) — see plan section 4.

## Architecture

### Backend: domain-driven layers under `app/Domain`

Business logic is organized by domain (currently only `Projects`), not by Laravel's default MVC folders:

```
app/Domain/Projects/
  Enums/         ProjectStatus, RobotType, ProjectMemberRole
  DTOs/          CreateProjectData, UpdateProjectData — typed inputs for services, no raw request arrays
  Contracts/      *RepositoryInterface — bound to Eloquent impls in AppServiceProvider::register()
  Repositories/  Eloquent* — implementations of the contracts above
  Services/      ProjectService — the only place transactional multi-step logic lives (DB::transaction)
```

Request flow: **Controller** (thin, route-model binding + Inertia response only) → **FormRequest** (`app/Http/Requests`, authorization + validation) → **Service** (`app/Domain/*/Services`, wraps writes in `DB::transaction`, records `ProjectActivity`) → **Repository** (`app/Domain/*/Repositories`, only place querying Eloquent directly) → **Model**.

When adding a new domain (Requirements, Resources, Memberships, GitHub per the plan), mirror this same Enums/DTOs/Contracts/Repositories/Services layout and register new repository bindings in `AppServiceProvider`.

### Authorization: two layers, never trust the frontend alone

1. **`EnsureProjectMember` middleware** (`project.member` route alias, `app/Http/Middleware/EnsureProjectMember.php`) — gates any route with a `{project}` param on `can('view', $project)`.
2. **`ProjectPolicy`** (`app/Policies/ProjectPolicy.php`) — fine-grained action checks (`update`, `archive`, `restore`, `delete`), all delegating to `Project::hasRole($user, [...roles])`.

Roles come from `ProjectMemberRole` (owner, manager, mechanical, electronics, software, contributor, viewer). The project owner is implicitly `Owner` even without a `project_members` row (see `Project::hasRole`). Follow this same middleware+policy pattern for new project-scoped resources.

### Frontend: Inertia + React, JSX legacy / TSX for new work

- `resources/js/app.jsx` is the Inertia entry point (see `vite.config.js` — `input: 'resources/js/app.jsx'`).
- Pre-existing Breeze auth/profile pages remain **`.jsx`**; new RoboForge business pages/components are written in **`.tsx`** (`resources/js/Pages/Projects/*.tsx`, `resources/js/Components/projects/*.tsx`, `resources/js/Components/ui/*.tsx`). Don't migrate old Breeze JSX unless asked.
- Path alias `@/*` → `resources/js/*` (configured in both `jsconfig.json` and `tsconfig.json`).
- `resources/js/types/projects.ts` holds shared TS types mirroring backend DTOs/enums — keep them in sync when the backend shape changes.

### `resources/template_frontend/` is a design reference only

This is a standalone Figma-Make React/Vite/Tailwind 4 prototype (its own `package.json`, TS config, `AGENTS.md`) with mock data and no persistence. It covers the full intended UX (auth, dashboard, project detail, 12-step CDC wizard, resources, team, GitHub) and is the source to extract visual language and component structure from — **never wire it up or run it as part of the main app**, and never copy its mock-data patterns. The main app uses Tailwind 3 / React 18, not this prototype's Tailwind 4 / React 19.

### Key domain model

- `Project` (ULID PK, soft-deletes): belongs to owner (`User`), has many `ProjectMember`, `ProjectTag`, `ProjectActivity`. Status/type/domain are enum-backed casts.
- `ProjectActivity` is an append-only audit log (`event`, `subject_type`/`subject_id`, `properties` JSON) written by services on every meaningful mutation — extend this pattern rather than adding ad hoc logging.
- Planned but not yet implemented (see plan doc for full schema): `requirements_documents` (versioned JSON CDC), `resources` (private-disk file metadata), `project_invitations`, `github_repositories`.

## Code conventions

- PHP: Laravel/PSR-12 conventions, enforced by Pint (`./vendor/bin/pint --test`).
- Naming: code identifiers (classes, routes, DB tables/columns, variables) in English; user-facing strings in French.
- New business React components: TypeScript (`.tsx`). Existing Breeze JSX pages stay JSX until deliberately migrated.
- Dates persisted as ISO/UTC where relevant, displayed per French locale. Money (when introduced) must be stored as minor-unit integers with a currency code — never floats.
- File resources are never stored under `public/`; they go on the private `local` disk and are served only through authorized controllers (see plan section 6 for the intended `projects/{project-ulid}/resources/{resource-ulid}` layout).
