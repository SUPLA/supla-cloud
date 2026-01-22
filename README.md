# supla-cloud

SUPLA is an open smart home platform for building and operating connected devices and systems.

`supla-cloud` is the **Cloud web application and REST API** of the SUPLA platform.  
It provides user-facing services such as configuration, automations, and integrations,
and is used by official SUPLA services as well as self-hosted deployments.

---

## What is SUPLA Cloud

This repository contains:
- the Cloud backend application,
- the REST API used by mobile apps, integrations, and partners,
- the web-based user interface.

It is a **core backend component** of the SUPLA ecosystem, but **not a complete SUPLA server stack on its own**.

A full, production-ready SUPLA installation also requires server components
responsible for device communication.

---

## SUPLA architecture overview

SUPLA consists of multiple components that together form a complete smart home platform,
including device firmware, server-side services, cloud applications, and deployment tooling.

For a high-level overview of the SUPLA architecture and how individual repositories
fit together, see the SUPLA organization repository:

👉 [github.com/SUPLA](https://github.com/SUPLA)

---

## Self-hosting and production use

For running your own SUPLA instance (recommended way for self-hosting and production),
use **SUPLA Docker**:

👉 [github.com/SUPLA/supla-docker](https://github.com/SUPLA/supla-docker)

`supla-docker` provides:
- a complete set of SUPLA services,
- Docker-based deployment,
- upgrade paths and operational configuration.

This repository is **not intended to be used directly for production deployments**.

---

## Development

This repository is intended for:
- SUPLA core contributors,
- backend and frontend developers,
- contributors working on the REST API or Cloud behavior.

For local development setup, see:

- [`Development.md`](./Development.md)

---

## REST API

SUPLA Cloud exposes a public REST API used by:
- mobile applications,
- third-party integrations,
- partner solutions.

API documentation is available via Swagger:

- [Swagger / API documentation](https://app.swaggerhub.com/apis/supla/supla-cloud-api/2.3.0)

Changes to public API endpoints must remain backward compatible whenever possible.

---

## Technology stack

- Backend: **Symfony**, **Doctrine**
- Frontend: **Vue.js**
- Deployment (production): **Docker** (via [`supla-docker`](https://github.com/SUPLA/supla-docker))

---

## Contributing

Contributions are welcome.

Please read:
- [`CONTRIBUTING.md`](./CONTRIBUTING.md) – contribution rules and workflow
- [`SECURITY.md`](./SECURITY.md) – how to report security issues

---

## Releases

Versioned releases and release notes are available on GitHub:

- [SUPLA Cloud releases](https://github.com/SUPLA/supla-cloud/releases)

---

## About SUPLA

For an overview of the SUPLA platform and ecosystem, see:
- [github.com/SUPLA](https://github.com/SUPLA)
- [www.supla.org](https://www.supla.org)

---

## License

This project is licensed under the **GPL-2.0** license.

