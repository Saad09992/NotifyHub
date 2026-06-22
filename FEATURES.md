# NotifyHub — Implemented Features

Snapshot of what the application can currently do.

---

## 1. Authentication (Sanctum)

| Endpoint | Method | Purpose |
|---|---|---|
| `/api/register` | POST | Create new user account |
| `/api/login` | POST | Issue API token |
| `/api/logout` | POST | Revoke all user tokens |
| `/api/user` | GET | Return authenticated user |

- Token-based via Laravel Sanctum (`personal_access_tokens` table).
- Passwords hashed with `Hash::make`.
- All `/api/*` routes except `register` / `login` require `auth:sanctum`.

---

## 2. Recipients

A recipient is a notification target owned by a user, with channel-specific contact details (email, slack_webhook).

| Endpoint | Method | Purpose |
|---|---|---|
| `/api/save-recipient` | POST | Create recipient |
| `/api/update-recipient/{recipient}` | PUT | Partial update of contact details |
| `/api/recipients` | GET | List authenticated user's recipients |
| `/api/recipients/{id}` | GET | Show single recipient |
| `/api/recipients/{recipient}` | DELETE | Delete recipient |

### Behavior
- `contact_details` stored as JSON via Eloquent `array` cast.
- Update merges new keys with existing (PATCH semantics) — slack_webhook update does not wipe email.
- Owner check on every read/update/delete: non-owner gets `404` (intentional — no info leak about existence).
- List scoped to `Auth::id()` — never returns other users' recipients.
- URL validation: `slack_webhook` must start with `https://hooks.slack.com/services/`.
- Email validation: must be valid format.

---

## 3. Templates

A template is reusable message body with placeholders and a list of supported delivery channels.

| Endpoint | Method | Purpose |
|---|---|---|
| `/api/save-template` | POST | Create template |
| `/api/templates/{template}` | PUT | Partial update (body and/or supported_channels) |
| `/api/templates` | GET | List authenticated user's templates |
| `/api/templates/{id}` | GET | Show single template |
| `/api/templates/{template}` | DELETE | Delete template |

### Behavior
- `template_body` stored as raw string with `{key}` placeholders.
- `supported_channels` stored as JSON array via cast.
- Update merges only provided fields (PATCH semantics).
- `supported_channels` values restricted to `[email, slack]` on save and update.
- Owner check on every read/update/delete: non-owner gets `404`.
- List scoped to `Auth::id()`.
- `compose()` interpolates `{key}` with values from payload, runs `htmlspecialchars` on each value to prevent XSS.
- Placeholders missing from payload remain literal `{key}` in output.

---

## 4. Notifications

Top-level entity tying recipient + template + channels + payload together.

| Endpoint | Method | Purpose |
|---|---|---|
| `/api/send-notification` | POST | Queue notification for delivery |
| `/api/notifications` | GET | List authenticated user's notifications (with attempts, newest first) |
| `/api/notifications/{id}` | GET | Show single notification with attempt history |
| `/api/notifications/{id}/status` | GET | Compact status payload (status + per-channel attempt summary) |

### Flow
1. Request validated (recipient_id, template_id, channels in `[email, slack]`, payload).
2. Controller fetches recipient + template (throws `RecipientNotFoundException` / `TemplateNotFoundException` on miss).
3. Notification row created with status `pending`.
4. `compose()` builds final message from template + payload.
5. `NotificationEvent` fired → `NotificationListener` builds one `SendNotificationJob` per channel.
6. Jobs dispatched as a Bus batch; finalizer dispatches `UpdateNotificationStatusJob`.
7. Each job creates a `NotificationAttempt` row, calls the channel, updates attempt status.
8. After all jobs finish, status job rolls up final notification status: `success`, `failure`, or `partial_success`.

---

## 5. Channels (Delivery Layer)

Channels implement `App\Contracts\NotificationContract::sendNotification($contact, string $message): string`.

### Email
- Uses `Mail::raw` (Symfony Mailer).
- Catches `TransportExceptionInterface`, wraps as `SendNotificationFailedException` with `previous` chain.
- Requires `contact_details.email`.

### Slack
- Posts to incoming webhook URL via `Http::post`.
- Catches `ConnectionException`, wraps as `SendNotificationFailedException`.
- Treats HTTP non-2xx as send failure.
- Requires `contact_details.slack_webhook`.

Both channels return `'delivered'` on success or throw on failure.

---

## 6. Queue / Job Layer

`SendNotificationJob` (one per channel per notification):

- `$tries = 3` — total attempts including first.
- `$timeout = 15` — kill stuck job after 15s.
- Attempt row created **before** try block — guaranteed to exist in catch.
- Catch hierarchy:
  1. `ChannelNotSupportedException` → permanent fail (`$this->fail()`), no retry.
  2. `SendNotificationFailedException` → mark attempt failed + rethrow → queue retries.
  3. `\Throwable` → mark attempt failed with generic reason + log critical + permanent fail.
- `failed()` method logs to `Log::critical` after all retries exhausted.

`UpdateNotificationStatusJob` runs after batch completion — recomputes parent notification status.

---

## 7. Error Handling

### Custom exception classes (`app/Exceptions/`)

| Class | HTTP | Purpose | `render()` | `report()` |
|---|---|---|---|---|
| `TemplateNotFoundException` | 404 | Template id miss or cross-tenant access | yes | false |
| `RecipientNotFoundException` | 404 | Recipient id miss or cross-tenant access | yes | false |
| `NotificationNotFoundException` | 404 | Notification id miss or cross-tenant access | yes | false |
| `ChannelNotSupportedException` | 422 | Unknown channel name passed to `resolveChannel` | yes | false |
| `SendNotificationFailedException` | — | Channel-layer failure, caught inside job | no | default |

All take `(string $context, int $code = 0, ?Throwable $previous = null)` constructor with `previous` chaining preserved.

### Central handler (`bootstrap/app.php`)

- Forces JSON responses on `/api/*` paths.
- Maps `Symfony\Component\HttpKernel\Exception\NotFoundHttpException` → unified JSON 404 (handles converted `ModelNotFoundException` from route-model binding or `findOrFail`).
- Maps `Illuminate\Auth\AuthenticationException` → unified JSON 401.

### Response shape

All API errors follow:
```json
{ "error": "<machine_code>", "message": "<human_text>", "<context_field>": "<value>" }
```

---

## 8. Models & Schema

- `User` — Sanctum-enabled, has many `Recipient`, `Template`, `Notification`.
- `Recipient` — JSON `contact_details`, belongs to user.
- `Template` — `template_body`, JSON `supported_channels`, belongs to user.
- `Notification` — links recipient + template, JSON `payload`, JSON `channels`, status enum (`pending`/`success`/`failure`/`partial_success`).
- `NotificationAttempt` — one per channel attempt, status (`pending`/`delivered`/`failed`) + `failure_reason`.
- `ConfiguredChannel`, `Secret` — defined, not yet wired into flow.

---

## 9. Validation

Each FormRequest enforces:

- `SendNotificationRequest`:
  - `recipient_id` int, `template_id` int, `channels` required array, `channels.*` in `[email, slack]`, `payload` array.
- `SaveRecipientRequest`:
  - `contact_details` required array.
- `SaveTemplateRequest`:
  - `template_body` required string, `supported_channels` required array.
- `UpdateRecipientRequest`:
  - All fields `sometimes`, inner email + slack_webhook URL validated when present.
- `UpdateTemplateRequest`:
  - `template_body` `sometimes` string, `supported_channels` `sometimes` array, each value restricted to `[email, slack]`.

---

## 10. What's NOT Implemented Yet

| Area | Missing |
|---|---|
| Validation | `recipient_id`/`template_id` lack `required` + `exists:` rules |
| Validation | Template's `supported_channels` not checked against requested channels at send time |
| Validation | Recipient `contact_details` inner keys not validated on save |
| Auth | Login returns HTTP 200 on bad credentials (should be 401) |
| Auth | No email verification, no password reset, no token expiry |
| Job | `$backoff` not set — retries fire instantly |
| Channels | `Http::timeout` not configured on Slack call (hangs possible) |
| Concurrency | No `lockForUpdate` in `updateNotificationStatus` (potential race on batch finalize) |
| Idempotency | No idempotency key on send-notification (retry risk) |
| Observability | No external monitoring (Sentry / Bugsnag) integration |
| Rate limiting | No throttling on any endpoint |
| Channels | `ConfiguredChannel` model exists but unused |
| Secrets | `Secret` model exists but unused |

---

## 11. File Layout

```
app/
├── Channels/              Email, Slack (implement NotificationContract)
├── Contracts/             NotificationContract interface
├── Events/                NotificationEvent
├── Exceptions/            4 domain exceptions
├── Http/
│   ├── Controllers/Api/   Auth, Recipient, Template, Notification
│   ├── Requests/          FormRequest validators
│   └── Resources/         JsonResource transformers
├── Jobs/                  SendNotificationJob, UpdateNotificationStatusJob
├── Listeners/             NotificationListener (fans out batch)
├── Models/                User, Recipient, Template, Notification, NotificationAttempt
└── Services/              Recipient, Template, Notification (business logic)
bootstrap/app.php          Central exception handler
routes/api.php             API routes
```
