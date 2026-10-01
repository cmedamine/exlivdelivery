# Current PHP Authentication Specification

**Project:** EXLIV Delivery PHP platform  
**Purpose:** provide the React landing-page project with an accurate description of the PHP platform's *current* authentication and authorization behaviour, plus the contract required to make the React application the single sign-in and registration entry point.  
**Status:** documentation of the code as inspected on 2026-09-30. It is not an API specification for the React application to call directly.

## 1. Executive summary

The PHP application currently owns its own username/password authentication. It is a traditional server-rendered PHP application:

- A browser submits credentials to `POST /login.php`.
- PHP validates the credentials against the MySQL `users` table.
- PHP starts a native PHP session and stores user identity and permissions in `$_SESSION`.
- Protected pages and AJAX handlers authorize the request by reading that PHP session.
- The application does **not** currently expose JSON login, OAuth/OIDC, JWT, refresh-token, or token-introspection endpoints.

For a React landing page to become the only login and registration UI, the PHP platform must receive and verify proof of the React application's authenticated identity, then create its own PHP session. The React browser session, local-storage values, or a client-supplied user object must never be trusted by PHP.

## 2. Current public entry points

| Purpose | Endpoint | Method | Current behaviour |
| --- | --- | --- | --- |
| Sign in | `/login.php` | `GET` | Renders the PHP login form. |
| Sign in | `/login.php` | `POST` | Accepts form fields `username` and `password`; `username` is treated as the email address. |
| Public registration | `/register.php` | `GET` | Renders the PHP customer registration form. |
| Public registration | `/register.php` | `POST` | Creates a pending `client` account after validation. |
| Log out | `/disconnect.php` | `GET` | Destroys the PHP session and redirects to `/login.php`. |
| Forgotten-password link | `/password.php` | `GET` | Linked by `login.php`, but no such file is present in this repository. There is no implemented password-reset flow. |

There is no public login or registration API intended for the React application.

## 3. Current login flow

### Request

`POST /login.php` uses `application/x-www-form-urlencoded` form data:

```text
username=<email address>
password=<plain-text password submitted over HTTPS>
rememberme=yes   # rendered by the form, but not used by server-side login code
```

`username` is trimmed and queried as an exact match against `users.email`. Email matching therefore follows the database collation rather than an explicit application-level normalization rule.

### Account checks

The login code fetches one record with:

```sql
SELECT * FROM users WHERE email = ? LIMIT 1
```

It grants access only when all of the following are true:

1. The submitted password verifies against the stored password, or exactly matches a legacy plain-text value.
2. `trash = '1'` (the account is not soft-deleted).
3. `active = 'on'` (the account is enabled/approved).

Failure messages are French:

- Invalid credentials: `E-mail ou mot de passe est incorrect`
- Soft-deleted/unavailable account: `Votre compte n'est pas disponible`
- Pending/disabled account: `Votre compte est en attente d'approbation par un administrateur.`

### Password handling

- The supported hash is bcrypt, created with `password_hash(..., PASSWORD_BCRYPT, ['cost' => 12])`.
- `password_verify()` checks bcrypt hashes.
- The login code has a compatibility path for legacy plain-text passwords. On a successful plain-text login it upgrades that specific value to bcrypt cost 12.
- Passwords are not exposed through a purpose-built authentication API.

### Successful session

On a successful login PHP calls `session_regenerate_id(true)` and populates these session keys:

| Session key | Source column | Meaning |
| --- | --- | --- |
| `id` | `users.id` | Internal PHP user ID. |
| `fullname` | `users.fullname` | Display name. |
| `picture` | `users.picture` | Profile image filename. |
| `phone` | `users.phone` | Phone number. |
| `email` | `users.email` | Email address. |
| `roles` | `users.roles` | Comma-separated permissions string. |
| `type` | `users.type` | Account type. |
| `upuser` | `users.upuser` when available | Parent user for worker accounts; see schema discrepancy below. |

It redirects:

- `moderator` users to `/index.php`
- all other account types to `/commands.php`

On later authenticated requests, `config.php` reloads the user from the database and refreshes most session values. This means changing `type`, `roles`, `active`, or `trash` must be considered by the new SSO bridge on every session-establishing login.

## 4. Current public registration flow

`POST /register.php` accepts:

| Field | Required | Current validation | Stored in |
| --- | --- | --- | --- |
| `fullname` | Yes | Non-empty | `users.fullname` |
| `email` | Yes | PHP `FILTER_VALIDATE_EMAIL`; must be unique | `users.email` |
| `phone` | Yes | Moroccan pattern `^0[5-7][0-9]{8}$` | `users.phone` |
| `password` | Yes | At least 6 characters; bcrypt cost 12 | `users.password` |
| `confirm_password` | Yes | Must exactly equal `password` | Not stored |
| `city` | No | Sanitized string; UI options come from active `cities` | `users.city` |
| `address` | No | Sanitized but not inserted by the current SQL statement | Not stored |

On success it creates:

```text
type       = client
roles      = Clients
active     = off
trash      = 1
picture    = avatar.png
datesignup = current Unix timestamp
```

The account cannot log in until an administrator changes `active` to `on`. The page displays a success message and sends a delayed redirect to `/login.php` after five seconds. It also writes an audit-log record when the `audit_log` table is available.

## 5. User identity and database model

### `users` table

The schema in `database_schema.sql` defines the following relevant columns:

| Column | Type / examples | SSO relevance |
| --- | --- | --- |
| `id` | integer, primary key | Existing internal identity. Never let React invent this value. |
| `email` | unique `varchar(255)` | Best current matching key for a React identity. Normalize consistently before matching. |
| `fullname` | `varchar(255)` | Display name. |
| `picture` | `varchar(255)` | Image filename, default `avatar.png`. |
| `password` | `varchar(255)` | Legacy PHP credential. It should no longer be used for interactive login after the migration. |
| `phone`, `city`, `cin`, `store`, `bank`, `rib`, `sav`, `stockout`, `emailstockout` | optional profile/business fields | Preserve; React registration may need to collect or defer required operational fields. |
| `type` | `moderator`, `dlm`, `subdlm`, `client`, `worker` | Primary account-type authorization input. |
| `roles` | text, comma-separated | Feature permissions, mainly for moderators. |
| `active` | `on` / `off` | PHP access gate; keep enforcing it. |
| `datesignup` | Unix timestamp | Account creation time. |
| `trash` | `1` active record / `0` soft-deleted | PHP access gate; keep enforcing it. |

**Schema note:** application code reads and writes `users.upuser` for worker ownership, but the checked-in `users` table definition does not contain this column. Confirm the production schema before any migration or user synchronization.

### Related tables

- `parametres.user` stores per-user UI preferences; it references `users.id`.
- `audit_log.user_id` records certain auditable actions and references `users.id`.
- `csrf_tokens.user_id` supports optional one-time CSRF tokens, though the current login and registration handlers do not use it.
- Many operational tables refer to users by their internal numeric ID (for example client, delivery-agent, worker, and sub-agent relationships). Do not replace PHP IDs with React IDs in those tables.

## 6. Account types and permissions

### Account types

| `type` | Intended role | Data scope used by PHP |
| --- | --- | --- |
| `moderator` | Administrator/back-office user | Broad access, further limited by `roles`. |
| `client` | Merchant/customer | Queries are generally limited to records where `client = session id`. |
| `dlm` | Delivery agent / delivery manager | Queries are generally limited to records where `dlm = session id`. |
| `subdlm` | Sub-delivery agent | Queries are generally limited to records where `subdlm = session id`. |
| `worker` | Confirmation agent/employee | Queries are generally limited to records where `worker = session id`. |

### Moderator permissions

`roles` is not a normalized relation or a token claim today. It is a single comma-separated string and page guards commonly use substring matching. The known named permissions are:

```text
Modérateurs
Agents de confirmation
Annonces
Villes
Livreurs
Frais de livraison
Ramassage Agences
Clients
Envois
Stocks
Etats
Emballages
Confirmation
Google Sheets
Ramassage
Commandes
Modification état commandes
BLS
Factures clients
Factures livreurs
Dépences
Réclamations
```

The special stored value `all` is expanded at runtime in `config.php` into the full named-permission list above.

### Authorization behaviour

- Most HTML pages call `session_start()`, include `config.php`, redirect unauthenticated visitors to `/login.php`, then perform their own type/role check.
- Unauthorized users are commonly redirected to `/404.php`.
- `ajax.php` requires a non-empty session `id` and `fullname` before processing actions.
- `ajax.php` has explicit server-side guards for moderator management actions (`Modérateurs`) and client management actions (`Clients`).
- Authorization is inconsistent across the legacy application: do not assume every AJAX action has an equivalent granular server-side permission check. The React app must not be treated as the authorization authority.

## 7. Internal account-management endpoints

The back-office UI creates and edits accounts using AJAX. These are internal browser endpoints, not a public registration API.

| Endpoint | Method | Action field | Account type created/managed |
| --- | --- | --- | --- |
| `/ajax.php` | `POST` | `adduser` | `moderator` |
| `/ajax.php` | `POST` | `adddlm` | `dlm` |
| `/ajax.php` | `POST` | `addsubdlm` | `subdlm` |
| `/ajax.php` | `POST` | `addclient` | `client` |
| `/ajax.php` | `POST` | `addworker` | `worker` |

Related actions support loading, soft deletion (`trash = '0'`), restore, and permanent deletion. Treat permanent deletion carefully because the schema has foreign keys from some user-related tables.

`ajax2.php` contains a near-duplicate legacy implementation. The current front-end JavaScript uses `ajax.php`; changes to authentication/account provisioning should identify whether `ajax2.php` is reachable in deployment before relying on that assumption.

### Important legacy password caveat

The public `/register.php` handler and `addclient` handler use bcrypt. Several other internal account-management actions (`adduser`, `adddlm`, `addsubdlm`, and `addworker`) write the supplied password directly. The login compatibility code supports those values and upgrades them only after a successful login.

The React project must **not** copy this behaviour or receive PHP passwords. After SSO is introduced, prefer provider-managed credentials and a trusted PHP session bridge.

## 8. Sessions, cookies, logout, and redirects

### Sessions

- Authentication state is stored in the server-side PHP session (`$_SESSION`).
- The native PHP session cookie name and flags use the PHP/server defaults; this repository does not configure `Secure`, `HttpOnly`, `SameSite`, session lifetime, or a custom session name.
- Session ID rotation occurs after a successful legacy password login.
- There is no explicit idle timeout or absolute session lifetime in the application code.

### Additional cookies

The UI renders a `rememberme` checkbox, but the login code does not implement persistent sign-in. A `rememberme` cookie is not created by the login handler.

`config.php` and `disconnect.php` attempt to expire cookies named `id`, `fullname`, `picture`, `phone`, `email`, `roles`, `type`, and sometimes `upuser`. The current login flow does not set these cookies, and the expiration code reads session values after destruction in `disconnect.php`. They must not be used as authentication evidence.

### Logout

`GET /disconnect.php` calls `session_destroy()` and redirects to `/login.php`. It does not notify any external identity provider. A future unified logout design must decide whether platform logout should also sign out of the React app, or only clear the platform session.

## 9. Security posture of the current implementation

This section is deliberately included so the React implementation does not extend legacy behaviour unintentionally.

| Topic | Current state | Required integration stance |
| --- | --- | --- |
| Transport | The code supports HTTP-derived URLs as well as HTTPS. | Deploy both applications over HTTPS only. |
| Login CSRF | No CSRF token on login. | Use state/nonce/PKCE for an authorization-code flow, or a signed one-time handoff mechanism. |
| General CSRF | Helpers and a DB table exist, but widespread use is not evident in inspected flows. | Protect state-changing PHP endpoints; use SameSite cookies as defense in depth, not as the only control. |
| Brute-force protection | No rate limiting, lockout, or login audit trail is implemented in `login.php`. | Let the identity provider enforce rate limits/MFA/abuse controls. |
| Password reset | Link exists but handler is missing. | Manage resets at the React app's identity provider after migration. |
| Password storage | Bcrypt cost 12 exists; legacy plain-text compatibility remains. | Never transmit, synchronize, or reimplement PHP passwords in React. |
| JWT/OIDC verification | No existing verifier or trusted issuer configuration. | Add server-side verification in PHP before creating its session. |
| Cookie flags | Relies on PHP defaults. | Set `Secure`, `HttpOnly`, and an appropriate `SameSite` setting explicitly. |
| Authorization | PHP roles/types remain the source for existing page guards. | Do not authorize PHP requests solely from React UI state or unverified claims. |

## 10. Recommended React-to-PHP SSO contract

### Preferred approach: Authorization Code + PKCE / OIDC

Use the React application's identity provider as the identity authority and add a small PHP callback/bridge endpoint. The React front end should redirect the browser to PHP only after authentication, while PHP verifies identity server-to-server or validates a signed ID token using the provider's published keys.

1. User visits a protected PHP page.
2. PHP redirects the browser to the React landing page or central identity-provider authorization endpoint with a validated return URL, `state`, nonce, and PKCE challenge where applicable.
3. The user signs in or registers in the React application.
4. The identity provider returns a short-lived authorization code to a PHP callback endpoint.
5. PHP exchanges the code server-to-server, validates issuer, audience, signature, expiration, nonce, and authorization response state.
6. PHP finds the local `users` row by a stable external subject ID (preferred) or, temporarily, normalized email.
7. PHP checks `active = 'on'` and `trash = '1'`, loads PHP-owned `type`/`roles`, regenerates the PHP session ID, and writes the session keys listed in section 3.
8. PHP redirects the user to the original safe internal path, `/index.php`, or `/commands.php`.

If the React app and PHP platform live on different parent domains, this redirect/callback approach works without trying to share cookies across sites.

### Do not use these approaches

- Do not give React access to the PHP database or PHP password hashes.
- Do not pass `id`, `roles`, `type`, or an unsigned JSON user object in a query string and trust it in PHP.
- Do not make PHP accept a token merely because it was posted by the browser; PHP must verify issuer, signature, audience, expiry, and anti-replay controls.
- Do not share a broad authentication cookie between unrelated applications/domains.
- Do not choose the PHP account only by mutable display name or phone number.

### Identity linking

Add a durable identity-provider binding to PHP before broad rollout, for example:

```sql
ALTER TABLE users
  ADD COLUMN auth_provider VARCHAR(100) NULL,
  ADD COLUMN external_subject VARCHAR(255) NULL,
  ADD UNIQUE KEY users_provider_subject (auth_provider, external_subject);
```

Recommended matching rules:

1. First sign-in: look up `(auth_provider, external_subject)`.
2. If absent, perform a carefully reviewed one-time match on normalized, verified email.
3. Bind the provider subject to the existing PHP account after confirmation.
4. On subsequent logins, use only the bound provider subject.
5. Do not automatically create privileged (`moderator`, `dlm`, `subdlm`, `worker`) accounts from a public React registration flow.

For new customers, either preserve the existing approval process (`type=client`, `active=off`) or explicitly decide that verified React registrations are auto-approved. This is a business-policy choice and must be made before implementation.

### Proposed verified identity payload

This is the minimum semantic payload PHP needs after it has verified the provider response. It is illustrative, not permission for the browser to call PHP with raw JSON:

```json
{
  "iss": "https://identity.example.com",
  "aud": "exliv-php-platform",
  "sub": "provider-stable-user-id",
  "email": "user@example.com",
  "email_verified": true,
  "name": "User Name",
  "iat": 0,
  "exp": 0,
  "nonce": "server-generated-value"
}
```

PHP should use `sub` for linking. It should continue to obtain `id`, `type`, `roles`, `active`, and `trash` from its own database, not from this payload.

## 11. Migration plan: retain but restrict PHP login and registration

The existing pages can remain in the repository as a rollback option while being unavailable to normal users.

### Phase 1 — Prepare

1. Choose the identity provider/mechanism used by the existing React landing page.
2. Add the provider-subject binding columns and a migration plan for existing users.
3. Implement a PHP callback/session bridge and tests for signature, issuer, audience, expiry, nonce/state, disabled accounts, and deleted accounts.
4. Configure explicit secure PHP session-cookie settings.
5. Inventory all deployment URLs and define allowlisted return URLs.

### Phase 2 — Enable SSO

1. Change unauthenticated PHP page redirects from `/login.php` to the SSO-start route.
2. Make `/login.php` redirect normal visitors to the React login route without accepting password POSTs.
3. Make `/register.php` redirect normal visitors to the React registration route without creating PHP accounts directly.
4. Keep a narrowly controlled emergency/maintenance legacy-auth switch, protected outside public routing and with an expiry date. Do not use a query parameter as the bypass.
5. Update `/disconnect.php` to clear the PHP session and redirect to the agreed React logout/login destination.

### Phase 3 — Validate and retire

1. Verify each account type has the correct PHP data scope after SSO session creation.
2. Verify suspended (`active=off`) and soft-deleted (`trash=0`) users cannot regain access through React.
3. Verify role changes take effect at the next PHP session bridge/login.
4. Monitor failed handoffs without logging credentials or full tokens.
5. After a defined rollback window, remove legacy password acceptance and the plain-text upgrade branch.

## 12. React project requirements checklist

- [ ] Identify the actual identity provider and authentication flow already used by the React landing page.
- [ ] Use HTTPS for all landing-page, identity-provider, and PHP URLs.
- [ ] Use authorization code + PKCE / OIDC where the provider supports it.
- [ ] Register a specific PHP callback URL and exact permitted logout/return URLs.
- [ ] Provide a stable immutable subject (`sub`) and a verified email claim.
- [ ] Do not send passwords to PHP or duplicate PHP password handling.
- [ ] Preserve PHP as the source of `type`, `roles`, `active`, `trash`, and numeric internal `id`.
- [ ] Decide customer provisioning/approval policy before enabling React registration.
- [ ] Add user-friendly handling for pending, disabled, and unavailable PHP accounts.
- [ ] Define whether one logout signs the user out of both applications.
- [ ] Add logging and alerting for failed token/code verification without recording tokens or secrets.
- [ ] Test all five PHP account types and every moderator permission set relevant to operations.

## 13. Relevant source files

| File | Responsibility |
| --- | --- |
| `login.php` | Legacy password login and PHP-session creation. |
| `register.php` | Public client registration and pending-account creation. |
| `disconnect.php` | Legacy PHP-session logout. |
| `config.php` | Database connection, user-session refresh, account scoping, settings. |
| `functions.php` | Password, validation, CSRF, and audit helper functions. |
| `ajax.php` | Active back-office AJAX account management plus some authorization guards. |
| `ajax2.php` | Legacy duplicate AJAX account-management implementation; review exposure. |
| `database_schema.sql` | Checked-in database schema, including `users`, `audit_log`, and `csrf_tokens`. |
| `js/script.js` | Browser-side form validation and AJAX requests. |

## 14. Secrets and configuration

The PHP application currently reads database configuration from `.env` when present:

```text
DB_HOST
DB_NAME
DB_USER
DB_PASSWORD
```

Do not copy secret values into this document or the React project. The SSO bridge will require new server-only configuration, typically an issuer URL, client ID, client secret where appropriate, redirect URI, allowed audiences, JWKS source, and cookie/session settings. Store those values only in environment configuration or the deployment secret manager.

## 15. Open decisions before implementation

1. What authentication provider/library does the published React landing page use?
2. What are the exact production URLs for the React app and the PHP platform, and do they share a parent domain?
3. Should a React registration create a pending PHP `client`, or should it be auto-approved?
4. Will all current PHP accounts be migrated and linked on first login, or will a controlled bulk-link process be used?
5. Which team members, if any, need a time-limited emergency legacy login after cutover?
6. Does signing out of the PHP platform also sign the user out of the React application/identity provider?

