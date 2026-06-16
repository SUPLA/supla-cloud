# AGENTS.md

- This repo is Symfony/Doctrine backend code in `src/` plus the Vue/Vite frontend in `frontend/`.
- The frontend source code is in `frontend/`
- Backend entrypoint is `public/index.php`; frontend build output goes to `public/dist`.

## Commands

- Repo-wide lint: `composer run lint` runs frontend lint first, then backend PHPCS.
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
- CI writes `.env.test.local` with `bin/write-ci-env.sh` before API/integration/measurement-log runs because shell DB env vars do not
  reliably reach PHP.
- CI waits for MariaDB/Postgres with `bin/wait-for-service.sh`; use the same pattern when reproducing those suites locally.
- `composer run collect-translations` runs backend translation generation first, then the frontend collector.
- Do not edit generated assets, caches, or `public/dist` by hand.
- Public API changes should be treated as backward-compatibility sensitive.
