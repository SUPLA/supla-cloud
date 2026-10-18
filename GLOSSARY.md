# SUPLA Cloud

The shared language for SUPLA Cloud concepts. Use these terms consistently in specifications, issues, code, tests, and operational documentation.

## OAuth authorization

**OAuth authorization request**:
The original OAuth parameters by which a third-party application requests authorization, including its client identity, callback, scopes, state, and optional PKCE parameters.
_Avoid_: OAuth URL, session target path

**Broker Cloud**:
The SUPLA Cloud instance that receives a public authorization request and coordinates trusted communication with the Cloud that owns the user's account. It may itself be that owning Cloud.
_Avoid_: redirect target, issuer (unless it actually issues the code)

**Target Cloud**:
The SUPLA Cloud instance that owns the user's identity and the applicable local OAuth client, and is authoritative for the user's authorization grant and authorization code.
_Avoid_: redirect target, remote server

**Public AD application**:
An application listed in the SUPLA Autodiscover public catalog and identified by a public client identifier. It is not necessarily an OAuth public client.
_Avoid_: public client

**OAuth public client**:
An OAuth client that cannot keep a client secret confidential. This is an OAuth security classification, independent of whether an application appears in the Autodiscover public catalog.
_Avoid_: public AD application

**Mapped client**:
A target Cloud's local representation of a Public AD application, whose identity and credentials are synchronized through Autodiscover and may be created when first needed.
_Avoid_: imported client, remote client

**Authorization grant**:
The durable consent that records the scopes one user has authorized for one local OAuth client. It is distinct from an authorization code.
_Avoid_: authorization code, login session

**Authorization code**:
A short-lived, single-use OAuth credential issued after a successful authorization request and exchanged for tokens by the application.
_Avoid_: access token, authorization grant

**Browser WebApp token**:
The bearer access token stored by the SUPLA WebApp and validated by its issuing Cloud. It contains target cloud address as the base64 encoded part. Any encoded Cloud address is a routing hint, not proof of user identity or permission for a backend network request.
_Avoid_: login session, target authorization
