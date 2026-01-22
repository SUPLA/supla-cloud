# Contributing to SUPLA Cloud

Thank you for your interest in contributing to **SUPLA Cloud**.
This document describes the rules and guidelines for contributing code,
documentation, and ideas to the project.

## Table of contents
- [Contributor License Agreement (CLA)](#contributor-license-agreement-cla)
- [Project scope and goals](#project-scope-and-goals)
- [Supported environments](#supported-environments)
- [Repository structure](#repository-structure)
- [Reporting issues](#reporting-issues)
- [Feature requests](#feature-requests)
- [Development guidelines](#development-guidelines)
- [Pull request process](#pull-request-process)
- [Licensing](#licensing)

---

## Contributor License Agreement (CLA)

This project requires acceptance of a **Contributor License Agreement (CLA)**.

Before a pull request can be merged, the CLA must be accepted by the contributor.

Pull requests with an unaccepted CLA will be blocked automatically.

---

## Project scope and goals

`supla-cloud` is the **Cloud web application and REST API** of the SUPLA smart home platform.

It is responsible for:
- user accounts and authentication,
- configuration and management of devices and channels,
- automations, schedules and reactions,
- REST API used by mobile apps, integrations and partners,
- web-based user interface.

Key goals:
- stability and backward compatibility of the API,
- predictable behavior for official and self-hosted deployments,
- clear separation between Cloud logic and server/device components,
- long-term maintainability.

This repository **does not** contain the full SUPLA server stack.
Production and self-hosted installations are typically managed using
`supla-docker`.

Changes should be made with production stability and upgrade paths in mind.

---

## Supported environments

### Runtime / production
- Docker-based deployments (via `supla-docker`)
- Linux-based servers and VPS environments

### Development
- PHP (Symfony)
- JavaScript / TypeScript
- Vue.js (frontend)

Exact versions and setup instructions are described in `Development.md`.

---

## Repository structure

This repository contains both backend and frontend components.

General rules:
- backend code is based on **Symfony** and **Doctrine**,
- frontend code is based on **Vue.js**,
- public REST API changes must be treated as backward-compatibility–sensitive,
- changes must not assume a specific deployment method
  (official cloud vs self-hosted).

If you are unsure where new code should be placed, feel free to open a pull
request and start a discussion there.

---

## Reporting issues

Before opening an issue:
1. Search existing issues (open and closed).
2. Verify that you are using a supported and up-to-date version.

When reporting a bug, please include:
- whether the issue occurs on:
  - official SUPLA Cloud, or
  - a self-hosted instance,
- SUPLA Cloud version (release tag or commit),
- deployment method (for self-hosted environments),
- steps to reproduce the issue,
- expected and actual behavior,
- relevant logs (with secrets removed).

Security-related issues **must not** be reported via public issues.
See `SECURITY.md`.

---

## Feature requests

Feature requests are welcome.

Please describe:
- the problem you are trying to solve,
- why existing functionality is insufficient,
- whether the change affects:
  - public REST API,
  - user interface,
  - self-hosted deployments,
- potential backward compatibility impact.

Large or breaking changes should be discussed in an issue before
implementation.

---

## Development guidelines

- Follow existing backend and frontend architecture patterns.
- Keep API changes backward compatible whenever possible.
- Avoid introducing breaking changes without prior discussion.
- Be explicit and readable rather than clever.
- Treat Cloud behavior as part of a production system used at scale.
- Assume both official cloud and self-hosted environments.

---

## Pull request process

1. Fork the repository and create a feature branch.
2. Make small, focused commits with clear commit messages.
3. Ensure the application builds and runs for the affected components.
4. Update documentation if required.
5. Open a pull request using the provided template.
6. Ensure the CLA check passes.

Each pull request should:
- address a single issue or feature,
- include a clear description of the changes,
- reference related issues if applicable.

---

## Licensing

By contributing to this repository, you agree that your contributions
will be licensed under the same license as the project.

Any third-party code must use a compatible license and be clearly documented.

---

Thank you for contributing to SUPLA Cloud.
