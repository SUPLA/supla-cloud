# supla-cloud

> Part of **SUPLA** — an open smart home platform that brings together hardware manufacturers, the community, and users.  
> Learn more at https://www.supla.org

`supla-cloud` is the **Cloud web application and REST API** of the SUPLA platform.  
It provides user-facing services such as configuration, automations, and integrations,
and is used by official SUPLA services as well as self-hosted deployments.

---

## What is SUPLA Cloud

This repository contains:
- the Cloud backend application,
- the REST API used by mobile apps, integrations, and partners,
- the web-based user interface (frontend).

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

### OCPP chargers (PoC)

Set `SUPLA_OCPP_PUBLIC_URL=wss://ocppX.supla.org` in the Cloud instance environment
to enable charger registration in **Integrations → Electric vehicle chargers**.
Use the public gateway base URL, without a path or charge point identity. The gateway
and its TLS reverse proxy must be deployed separately; Cloud only supplies configuration.
An empty value disables registration.

Apply the OCPP database schema and install the `supla_v_ocpp_charging_station` view
before enabling this feature. Each registration atomically creates one virtual device,
an electricity meter, a charging switch, and the station credentials. No initial channel
values are inserted. Chargers count towards the device limit, not the data source limit.

`is_virtual` remains a boolean compatible with supla-server. The additive API field
`virtualType` is `OCPP` for devices associated with a charging station, `DATA_SOURCE`
for other virtual devices, and `null` for physical devices. Channels inherit this type.
The cyclic data-source updater excludes OCPP devices.

The configuration dialog shows the full WSS URL, base URL, identity, Basic Auth username,
and a one-time 32-character hexadecimal AuthorizationKey. Copying the key omits display
separators. Only the SHA-512 hash of the **binary** key is stored. Lost keys can be replaced;
update and reconnect the charger afterwards. The gateway periodically revalidates
existing sessions; a key replacement does not close them immediately. The gateway
view excludes disabled devices. Refresh its definition with
`supla:initialize:create-sql-procedures-and-views` when upgrading an existing PoC.

### Full SUPLA stack

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

- [API documentation](https://svr1.supla.org/api-docs/docs.html)

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

SUPLA Cloud releases are tagged using CalVer versioning variant: 0Y.0M[.MICRO] (see [calver.org](https://calver.org/)).

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
