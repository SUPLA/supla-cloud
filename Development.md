# SUPLA Cloud - Development

This document describes how to run **SUPLA Cloud** in a local **development environment**.

It does **not** apply to production or self-hosted deployments.

Please note that this setup installs **SUPLA Cloud only**, without the device communication server
([supla-server](https://github.com/SUPLA/supla-core/tree/master/supla-server)).

Managing real devices using SUPLA Cloud alone is not possible. By default, the development environment uses a
**mocked supla-server** for local testing.

---

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
cd src/frontend
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

