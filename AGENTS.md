# AGENTS.md

- This repo is Symfony/Doctrine backend code in `src/` plus the Vue/Vite frontend in `frontend/`.
- The frontend source code is in `frontend/`
- Backend entrypoint is `public/index.php`; frontend build output goes to `public/dist`.

## Commands

- Repo-wide lint: `composer run lint` fixes frontend and backend lint violations in place, then checks YAML files in `config/`. It exits
  non-zero when fixes are applied or violations remain.
- Backend lint: `composer run lint-backend`.
- Frontend lint: `cd frontend && npm run lint` (it fixes in place).
- Frontend format check: `cd frontend && npm run format:check`.
- Backend tests: `./vendor/bin/phpunit --testsuite unit|integration|api|measurement_logs`.
- Frontend unit tests: `cd frontend && npm run test:unit`.
- Frontend e2e: `cd frontend && npm run test:e2e:ci`.
- Frontend dev server: `cd frontend && npm run dev` on `http://localhost:25787`, proxied to backend on `127.0.0.1:8008`.
- Frontend build: `composer run webpack` or `cd frontend && npm run build`; the build clears `public/dist` first.
- Static analysis: `vendor/bin/phpstan analyse -c phpstan.dist.neon`.

## Workflow Notes

- `phpunit.dist.xml` defines the test suites; `integration`, `api`, and `measurement_logs` are separate suites.
- before running the tests, clear the var/cache/test directory.
- Before running backend integration/API/measurement-log tests, the coordinator assigns each concurrently running agent or test process a unique `XXX`, using an agent number or issue number. Set `DATABASE_NAME=supla_agent_XXX` (without `_test`); Doctrine's test config appends the test suffix, so the database is exactly `supla_agent_XXX_test` (for example, issue 09 uses `DATABASE_NAME=supla_agent_09`). The coordinator owns assignment and ensures no concurrent process shares the same name. Use this convention in `.env.test.local` as well as shell environment, since that file can take precedence. Do not use the shared `supla_test` database for agent runs; integration tests drop and recreate their assigned database.
- CI writes `.env.test.local` with `bin/write-ci-env.sh` before API/integration/measurement-log runs because shell DB env vars do not
  reliably reach PHP.
- CI waits for MariaDB/Postgres with `bin/wait-for-service.sh`; use the same pattern when reproducing those suites locally.
- `composer run collect-translations` runs backend translation generation first, then the frontend collector. Do not run it unless explicitly asked.
- Write frontend text in English and use Vue I18n tags and functions so it can be collected later.
- New Vue components must use Vue 3 compatibility mode: set `compatConfig: { MODE: 3 }` on the component.
- When modifying an existing Vue component, migrate it to Vue 3 syntax and set the appropriate compatibility mode when the change is straightforward. Check its usages and preserve its behavior so the migration does not break consumers.
- Do not write database migration files. When a schema change is needed, edit the Doctrine entities only; the user generates migrations later.
- Do not edit generated assets, caches, or `public/dist` by hand.
- Public API changes should be treated as backward-compatibility sensitive.

## Agent skills

### Issue tracker

Issues and specs are maintained as local Markdown files under `.scratch/`. See `docs/agents/issue-tracker.md`.

### Domain docs

This repository uses a single-context domain-documentation layout. See `docs/agents/domain.md`.
