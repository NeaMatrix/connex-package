# neamatrix/connex

Laravel package for Connex DCB login: `auth-login` → `protected-script` (`#cta_button`) → `DCBProtectRun` → `gateway-load` → `login-connex`.

## Database (users table)

Package migrations add Connex subscriber fields from OTP confirm `success`:

| Column | Example | Source |
|--------|---------|--------|
| `msisdn` | `218920920110` | `success.msisdn` |
| `subscriber` | `exist` | `success.subscriber` |
| `status` | `active` | `success.status` |
| `operator` | `Libyana` | `success.operator` |
| `expiration_date` | `2032-08-11 15:56:54` | `success.expiration_date` |

```bash
php artisan migrate
```

Migrations live in `packages/torgodly/connex/database/migrations/` (auto-loaded by the package).

Ensure your `User` model includes these in `$fillable` (and `expiration_date` cast as `datetime`).

## Tests

From the Laravel app root:

```bash
php artisan test
```

Covers URL building, upstream HTTP (faked), user sync, OTP confirm API, Sanctum token issuance, and the login page.

## Requirements

- PHP 8.2+
- Laravel 10, 11, 12, or 13
- [laravel/sanctum](https://github.com/laravel/sanctum) (recommended for mobile API tokens after OTP confirm)

## Install

```bash
composer require neamatrix/connex
php artisan vendor:publish --tag=connex-config
php artisan migrate
```

Register on [Packagist](https://packagist.org/): submit `https://github.com/NeaMatrix/connex-package` after pushing this repo.

### Local path (development)

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/torgodly/connex",
        "options": { "symlink": true }
    }
],
"require": {
    "neamatrix/connex": "@dev"
}
```

## Environment

### Upstream endpoints

Each call uses `CONNEX_BASE_URL` + `CONNEX_*_ENDPOINT`.

| Purpose | Env | Default |
|--------|-----|---------|
| Auth token | `CONNEX_AUTH_LOGIN_ENDPOINT` | `/auth-login` |
| DCB script | `CONNEX_PROTECTED_SCRIPT_ENDPOINT` | `/protected-script` |
| Send OTP | `CONNEX_LOGIN_CONNEX_ENDPOINT` | `/login-connex` |
| Confirm OTP | `CONNEX_LOGIN_CONFIRM_ENDPOINT` | `/login-confirm` |

```env
CONNEX_BASE_URL=https://jobassistant.mooo.com
CONNEX_AUTH_LOGIN_ENDPOINT=/auth-login
CONNEX_PROTECTED_SCRIPT_ENDPOINT=/protected-script
CONNEX_LOGIN_CONNEX_ENDPOINT=/login-connex
CONNEX_LOGIN_CONFIRM_ENDPOINT=/login-confirm
```

```env
CONNEX_AUTH_EMAIL=your@api.user
CONNEX_AUTH_PASSWORD=secret
CONNEX_WEB_LOGIN_PATH=/connex/login
CONNEX_DEBUG_LOG=true
CONNEX_SUBMIT_BUTTON_ID=cta_button
```

## Default login page

Package registers `GET {CONNEX_WEB_LOGIN_PATH}` (default `/connex/login`) → `connex::login`.

## Custom login page

**Option A — Blade component (recommended)**

Publish views (optional):

```bash
php artisan vendor:publish --tag=connex-views
```

Create `resources/views/my-login.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>My brand login</title>
</head>
<body>
    <x-connex-login title="Welcome">
        <x-slot:form>
            {{-- Required IDs from config (default: msisdn, cta_button, transaction_identify) --}}
            <input id="msisdn" type="tel" placeholder="Phone">
            @include('connex::partials.hidden-fields')
            <button id="cta_button" type="button" disabled
                data-connex-label-sign-in="Continue"
                data-connex-label-loading="Please wait…"
                data-connex-label-signing-in="Sending OTP…">
                Please wait…
            </button>
        </x-slot:form>
    </x-connex-login>
</body>
</html>
```

Point the package at your view:

```env
CONNEX_LOGIN_VIEW=my-login
```

**Option B — Include scripts only**

Keep your own HTML and add at the bottom:

```blade
@include('connex::partials.hidden-fields')
@include('connex::partials.debug-log')
@include('connex::partials.scripts')
```

Required elements (configurable via `config/connex.php` → `selectors`):

| Purpose | Default id / name |
|--------|-------------------|
| MSISDN input | `#msisdn` |
| Submit button | `#cta_button` (API gets `#cta_button`) |
| Transaction | `#transaction_identify` |
| Device type | `name="device_type"` value `web` |

## OTP + mobile login

1. User submits phone → `login-connex` (OTP sent).
2. UI **automatically switches** to the OTP field (`#otp` in `#connex_otp_step`; button label becomes “Verify OTP”).
3. User submits OTP → `POST /connex/api/confirm-otp` (Laravel):
   - Calls upstream `login-confirm` with `msisdn` + `otp`
   - Creates/updates local user from Connex `success` payload
   - Issues Sanctum token for the mobile app
   - Runs your `HandlesOtpConfirmation` handler for custom JSON

### Custom OTP handling

Implement `Torgodly\Connex\Contracts\HandlesOtpConfirmation` and bind it:

```php
$this->app->bind(HandlesOtpConfirmation::class, YourHandler::class);
```

```php
public function handle(Authenticatable $user, array $connexSuccess): array
{
    // Return extra keys merged into the API response (redirect URLs, app flags, etc.)
    return ['app' => ['tier' => 'premium']];
}
```

### Mobile API response (`POST /connex/api/confirm-otp`)

```json
{
  "messageCode": "00",
  "success": { "message": "OTP confirmed", "connex": { "msisdn": "218...", "status": "active", ... } },
  "auth": {
    "token": "1|plainTextToken...",
    "token_type": "Bearer",
    "expires_at": null,
    "user": { "id": 1, "msisdn": "218...", "status": "active", "operator": "Libyana", "expiration_date": "..." }
  },
  "app": { }
}
```

Mobile app: store `auth.token`, send `Authorization: Bearer {token}` on API calls.

### Web hook

```javascript
document.addEventListener('connex:authenticated', function (e) {
  console.log(e.detail.auth.token);
});
```

## Security note

Credentials are rendered into `ConnexLoginConfig` for browser `auth-login`. OTP confirm runs server-side. For production, consider moving OTP request server-side too.
