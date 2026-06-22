# NotifyHub

A multi-tenant notification dispatch service. Users define recipients and message templates, then send notifications through one or more channels (email, Slack). Delivery happens asynchronously through a queued job pipeline with per-channel attempt tracking, retry logic, and aggregate status rollup.

Built on Laravel 12 with Sanctum token authentication.

---

## What It Does

1. **Authenticate** — users register and obtain an API token.
2. **Configure** — save recipients (with channel-specific contact details) and reusable message templates with `{placeholder}` syntax.
3. **Send** — POST a notification specifying a recipient, template, payload, and channels. The HTTP request returns immediately with `queued` status.
4. **Deliver** — a per-channel job fans out, attempts delivery, records the outcome on a `notification_attempts` row.
5. **Observe** — query the notification's status endpoint to see whether delivery succeeded, partially succeeded, or failed, with per-channel breakdown.

---

## Architecture

### Layer Map

```
   HTTP boundary
   ─────────────
   Controllers          ← request validation, response shaping
        │
        ▼
   Services             ← business rules, owner checks, DB writes
        │
        ▼
   Models (Eloquent)    ← persistence, JSON casts, relationships
        │
   ━━━━━━━━━━━━━━━━━━━━━━ async boundary (event → batch jobs)
        │
        ▼
   Jobs                 ← retry policy, attempt-row updates
        │
        ▼
   Channels             ← external IO (Mail, HTTP), vendor exception wrapping
```

### Send Pipeline

```
POST /api/send-notification
        │
        ▼
SendNotificationRequest   (validate)
        │
        ▼
NotificationController    (fetch recipient + template, create notification row, compose message)
        │
        ▼
NotificationEvent         (dispatched synchronously)
        │
        ▼
NotificationListener      (one SendNotificationJob per channel, packed into a Bus::batch)
        │
        ▼
[queue worker]            (parallel execution per channel)
        │
        ▼
SendNotificationJob       (attempt row → resolveChannel → channel->sendNotification → update row)
        │
        ▼
Bus batch finally()       (UpdateNotificationStatusJob dispatched)
        │
        ▼
UpdateNotificationStatusJob   (rolls up attempt outcomes → notification status)
```

### Key Decisions

| Decision | Reasoning |
|---|---|
| **Event + batch** instead of direct dispatch | Lets us treat channel jobs as a logical group and run finalization once the whole batch completes. |
| **Attempt row created before channel call** | Guarantees the catch block has a row to update; never lose attempt context on failure. |
| **Channels translate vendor exceptions internally** | Jobs only need to catch `SendNotificationFailedException` instead of a growing list of vendor types. New channels don't pollute the job's catch clauses. |
| **Owner check inside service `getX()`** | Single source of truth. Read/update/delete paths all benefit; no policy duplication across controllers. |
| **404 for cross-tenant access** | A 403 confirms the resource exists. Returning 404 hides existence — standard multi-tenant practice. |
| **Snake_case JSON keys, camelCase PHP identifiers** | Matches Laravel convention; API contract reads naturally for clients without forcing PHP-side rename. |
| **Custom exception classes own their `render()`** | Per-class HTTP shape; controllers stay thin, no `if/else` ladders mapping exceptions to status codes. |
| **`NotFoundHttpException` mapping in central handler** | Laravel converts `ModelNotFoundException` before render closures fire. Catching the converted form (and inspecting `getPrevious()`) is the clean intercept point. |
| **`array_merge` on update** | PATCH semantics by default. Updating `slack_webhook` doesn't wipe `email`. |
| **Job `$tries = 3`, no `$backoff` yet** | Conservative retry count; backoff configuration pending. |

---

## Domain Model

```
User ─┬─< Recipient ─┐
      │              │
      ├─< Template ─┐│
      │             ││
      └─< Notification ─< NotificationAttempt
                    ▲
                    └─── one row per (notification, channel) pair
```

| Model | Stores |
|---|---|
| `User` | Auth identity, Sanctum tokens |
| `Recipient` | JSON `contact_details` (`{email, slack_webhook}`) |
| `Template` | Body with `{placeholders}`, JSON `supported_channels` |
| `Notification` | recipient + template + payload + channels + rolled-up status |
| `NotificationAttempt` | One row per channel attempt, status (`pending` / `delivered` / `failed`) + `failure_reason` |

---

## Error Handling

### Exception Hierarchy

| Exception | Layer | HTTP | Notes |
|---|---|---|---|
| `TemplateNotFoundException` | service | 404 | renders structured JSON, suppresses log |
| `RecipientNotFoundException` | service | 404 | renders structured JSON, suppresses log |
| `NotificationNotFoundException` | service | 404 | renders structured JSON, suppresses log |
| `ChannelNotSupportedException` | service | 422 | unknown channel name |
| `SendNotificationFailedException` | channel/job | — | caught inside job, never reaches HTTP layer |

All inherit from `Exception`, support exception chaining via `previous`.

### Job Catch Hierarchy

```php
try {
    // resolve + send
} catch (ChannelNotSupportedException $e) {
    // permanent → $this->fail($e)
} catch (SendNotificationFailedException $e) {
    // transient → rethrow → queue retries
} catch (\Throwable $e) {
    // unknown bug → log critical + $this->fail($e)
}
```

The `failed()` method logs to `Log::critical` after retries exhaust.

### Central Handler (`bootstrap/app.php`)

- Forces JSON responses on `/api/*`.
- Maps `NotFoundHttpException` (post-conversion from `ModelNotFoundException`) to unified JSON 404.
- Maps `AuthenticationException` to unified JSON 401.

All API errors follow:

```json
{
  "error": "<machine_code>",
  "message": "<human_text>",
  "<context_field>": "<value>"
}
```

---

## API Reference

All routes require `auth:sanctum` except `/register` and `/login`.

### Auth

| Method | Path | Purpose |
|---|---|---|
| POST | `/api/register` | Create user |
| POST | `/api/login` | Issue API token |
| POST | `/api/logout` | Revoke tokens |
| GET | `/api/user` | Authenticated user |

### Recipients

| Method | Path | Purpose |
|---|---|---|
| POST | `/api/save-recipient` | Create |
| GET | `/api/recipients` | List (scoped to authenticated user) |
| GET | `/api/recipients/{id}` | Show |
| PUT | `/api/update-recipient/{recipient}` | Partial update (merge) |
| DELETE | `/api/recipients/{recipient}` | Delete |

### Templates

| Method | Path | Purpose |
|---|---|---|
| POST | `/api/save-template` | Create |
| GET | `/api/templates` | List |
| GET | `/api/templates/{id}` | Show |
| PUT | `/api/templates/{template}` | Partial update |
| DELETE | `/api/templates/{template}` | Delete |

### Notifications

| Method | Path | Purpose |
|---|---|---|
| POST | `/api/send-notification` | Queue notification |
| GET | `/api/notifications` | List (newest first, attempts eager-loaded) |
| GET | `/api/notifications/{id}` | Show with attempt history |
| GET | `/api/notifications/{id}/status` | Compact status + per-channel summary |

---

## Channels

### Email

- Backend: `Mail::raw` (Symfony Mailer).
- Catches `TransportExceptionInterface`, wraps as `SendNotificationFailedException` with `previous` chain preserved.
- Requires `contact_details.email`.

### Slack

- Backend: Slack [Incoming Webhooks](https://api.slack.com/messaging/webhooks) via `Http::post`.
- Catches `ConnectionException`, wraps as `SendNotificationFailedException`.
- Treats non-2xx responses as failure.
- Requires `contact_details.slack_webhook` (URL beginning with `https://hooks.slack.com/services/`).

Both channels return the string `'delivered'` on success or throw on failure.

---

## Setup

### Prerequisites

- PHP 8.2+
- Composer
- A queue backend (database / Redis)
- A mail backend (`MAIL_MAILER` — `smtp`, `log`, `array`, etc.)
- SQLite, MySQL, or PostgreSQL

### Install

```bash
git clone <repo>
cd NotifyHub
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Run

```bash
# HTTP server
php artisan serve

# Queue worker (separate terminal)
php artisan queue:work
```

### Quick Smoke Test

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Accept: application/json" \
  -d '{"name":"test","email":"test@example.com","password":"secret123"}'

# Login → grab token
TOKEN=$(curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"secret123"}' \
  | jq -r .token)

# Save recipient
curl -X POST http://localhost:8000/api/save-recipient \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -d '{"contact_details":{"email":"to@example.com"}}'

# Save template
curl -X POST http://localhost:8000/api/save-template \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -d '{"template_body":"Hello {name}","supported_channels":["email"]}'

# Send
curl -X POST http://localhost:8000/api/send-notification \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -d '{"recipient_id":1,"template_id":1,"channels":["email"],"payload":{"name":"World"}}'
```

---

## Known Limitations

### Validation
- `SendNotificationRequest.recipient_id` and `template_id` lack `required` and `exists:` rules — a missing or fabricated ID currently surfaces as a service-layer 404 instead of a 422.
- Template's `supported_channels` is not cross-checked against the requested channels at send time. The job will eventually fail per attempt, but the failure could be caught earlier in validation.
- `SaveRecipientRequest` accepts any shape of `contact_details`. Inner keys are not validated at save time (only at update time).

### Auth
- Login returns HTTP 200 on bad credentials. Should return 401.
- No email verification flow.
- No password reset.
- No token expiry or refresh — tokens valid until manual revocation.

### Job / Queue
- `$backoff` is not configured on `SendNotificationJob`. Failed jobs retry immediately, which can hammer a recovering external service.
- No idempotency key on `/send-notification`. A client retrying a hung request can dispatch duplicate notifications.

### Channels
- No `Http::timeout()` on the Slack call. A stalled Slack endpoint can hold a worker until job `$timeout` fires.
- Webhook URL is logged only as identifier — but error messages historically leaked it. Audit any new error string referencing `$contact`.

### Concurrency
- `updateNotificationStatus` reads attempt rows without `lockForUpdate`. In theory two batch-finalize jobs could race; currently no such dispatch path exists.

### Operational
- No external monitoring (Sentry / Bugsnag) wired up. Logs are local.
- No rate limiting on any endpoint.
- List endpoints return all rows — no pagination.

### Domain Gaps
- `ConfiguredChannel` and `Secret` models exist but are not yet integrated into the channel-resolution flow.
- No DELETE endpoint for users.

---

## File Layout

```
app/
├── Channels/                  EmailChannel, SlackChannel (implement NotificationContract)
├── Contracts/                 NotificationContract
├── Events/                    NotificationEvent
├── Exceptions/                Domain exceptions (Template/Recipient/Notification/Channel/Send)
├── Http/
│   ├── Controllers/Api/       Auth, Recipient, Template, Notification
│   ├── Requests/              FormRequest validators
│   └── Resources/             JsonResource transformers (Save / Update / generic)
├── Jobs/                      SendNotificationJob, UpdateNotificationStatusJob
├── Listeners/                 NotificationListener (fans out to batch)
├── Models/                    User, Recipient, Template, Notification, NotificationAttempt
└── Services/                  RecipientService, TemplateService, NotificationService
bootstrap/app.php              Central exception handler + JSON-on-/api forcing
routes/api.php                 API routes
```

---

## License

MIT.
