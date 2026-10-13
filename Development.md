# SUPLA Cloud - Development

This document describes how to run **SUPLA Cloud** in a local **development environment**.

It does **not** apply to production or self-hosted deployments.

Please note that this setup installs **SUPLA Cloud only**, without the device communication server
([supla-server](https://github.com/SUPLA/supla-core/tree/master/supla-server)).

Managing real devices using SUPLA Cloud alone is not possible. By default, the development environment uses a
**mocked supla-server** for local testing.

---

## Energy cost reporting

The energy-cost calculation and simulation endpoints request the calculator's optional
`reportSummary: {costs, fixedChargesComplete}`. The Costs report uses its gross total,
included fixed charges and taxes; existing strict `costs` and usage-only chart facts
remain available for detail/comparison consumers. This integration requires calculator
0.11.0 once released; update the Composer requirement and lockfile together then.

The calculator receives the original user-selected half-open `[from, to)` range in UTC.
Date-only UI end dates are inclusive and become the next local midnight, including DST
offset changes. Neither the backend nor the picker clips selections to measurements.
Usage is priced only from available measurements, without extrapolation. Measurement
availability information is shown separately from skipped-charge errors and omitted
fixed charges.

Fixed charges are included only for fully covered effective billing periods. The report
offers **Expand** for one partial period and **Align** for multiple overlapping periods
with partial edges, only when `fixedChargesComplete` is false. Both actions use the
first/last `billingPeriods[]` boundaries and update the picker, saved range, URL and
calculation request. Historical, ongoing and future periods follow the same rules.

Regression coverage is in `tests/Model/EnergyCostPlanCalculatorTest.php` and the frontend
`energy-cost-summary` / `energy-cost-comparison-dashboard` unit tests. When testing
before the release, load the adjacent calculator with a temporary development autoloader
or local Composer path repository; do not retain dependency overrides in version control.

## First launch

### Prerequisites

1. Install **PHP 8.3 or newer**

   * On Windows, you may use tools such as [XAMPP](https://www.apachefriends.org/) or equivalent PHP distributions.

2. Install a **MySQL-compatible database server**

   * MySQL or MariaDB
   * XAMPP already includes MySQL.

3. Install **Node.js LTS**

   * Current LTS version recommended (includes npm).

4. Install **Git**, then fork and clone the repository:

```bash
git clone https://github.com/YOUR_FORK/supla-cloud.git
```

5. Go to the cloned directory.

6. Install **Composer**:
   [https://getcomposer.org/download/](https://getcomposer.org/download/)

---

### Backend setup

7. Install PHP dependencies:

```bash
composer install
```

During installation, Composer will ask for configuration values. For development purposes, the most important are:

* `database_*` – database connection configuration
* `supla_server` – set to `localhost:8008`
* `recaptcha_enabled` – set to `false`

Other values can be left at their defaults.

If you need to adjust the configuration later, edit:

`app/config/parameters.yml`

---

### Frontend setup

8. Install frontend dependencies and build the frontend for the first time:

```bash
composer run-script webpack
```

---

### Database initialization

9. Initialize the database and load sample data:

```bash
php bin/console cache:clear --no-warmup -e dev
php bin/console supla:dev:dropAndLoadFixtures -e dev
```

---

## Development workflow

1. Run the backend application:

```bash
php -S 127.0.0.1:8008 -t web web/router.php
```

2. Run the frontend development server:

```bash
cd frontend
npm run serve
```

3. Open the application in your browser:

`http://localhost:8080`

4. Log in using the sample account:

   * email: `user@supla.org`
   * password: `pass`

---

## Notes on scope

* This setup is intended **only for development and testing**.
* It uses a mocked device server and does not support real device connections.
* For production or self-hosted installations, use:
  [https://github.com/SUPLA/supla-docker](https://github.com/SUPLA/supla-docker)

---

## Contributing

If you would like to contribute:

1. Check existing issues:
   [https://github.com/SUPLA/supla-cloud/issues](https://github.com/SUPLA/supla-cloud/issues)
2. Comment on an issue to indicate you are working on it.
3. Create a feature branch on your fork.
4. Submit a pull request.
5. Follow the rules described in `CONTRIBUTING.md`.

Thank you for contributing to the SUPLA project.
