# neamatrix/connex

Laravel package for Connex DCB login: server-side `auth-login` + `protected-script`, browser-only `dcbprotect` / `gateway-load`, server-side `login-connex` + `login-confirm-connex`, customizable login UI, mobile Sanctum tokens.

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

### From Packagist (after package is submitted)

Submit `https://github.com/NeaMatrix/connex-package` on [Packagist.org](https://packagist.org/), then:

```bash
composer require neamatrix/connex:^1.1
php artisan vendor:publish --tag=connex-config
php artisan migrate
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### Before Packagist / dev install

If you see *"Could not find a version matching minimum-stability (stable)"*, use the GitHub repo directly:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/NeaMatrix/connex-package.git"
    }
]
```

```bash
composer require neamatrix/connex:^1.1
```

Or allow dev stability once:

```bash
composer require neamatrix/connex:dev-main
```

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
| Confirm OTP | `CONNEX_LOGIN_CONFIRM_ENDPOINT` | `/login-confirm-connex` |

```env
CONNEX_BASE_URL=https://jobassistant.mooo.com
CONNEX_AUTH_LOGIN_ENDPOINT=/auth-login
CONNEX_PROTECTED_SCRIPT_ENDPOINT=/protected-script
CONNEX_LOGIN_CONNEX_ENDPOINT=/login-connex
CONNEX_LOGIN_CONFIRM_ENDPOINT=/login-confirm-connex
```

```env
CONNEX_AUTH_EMAIL=your@api.user
CONNEX_AUTH_PASSWORD=secret
CONNEX_WEB_LOGIN_PATH=/connex/login
CONNEX_DEBUG_LOG=true
CONNEX_SUBMIT_BUTTON_ID=cta_button
```

## Browser vs server (v1.1+)

| Step | Where it runs |
|------|----------------|
| `auth-login`, `protected-script`, `login-connex` | **Laravel** (`POST /connex/api/bootstrap`, `POST /connex/api/request-otp`) |
| `dcbprotect` script + `DCBProtectRun` + `gateway-load` | **Browser** (antifraud; credentials never sent to the client) |
| `login-confirm-connex` | **Laravel** (`POST /connex/api/confirm-otp`) |

`ConnexLoginConfig` exposes only Laravel API URLs + CSRF + selectors — not `CONNEX_AUTH_EMAIL`, `CONNEX_AUTH_PASSWORD`, or upstream URLs.

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
| Submit button | `#cta_button` — must stay **outside** hidden phone/otp step wrappers (default layout does this) |
| Transaction | `#transaction_identify` |
| Device type | `name="device_type"` value `web` |

## OTP + mobile login

1. Page load → `POST /connex/api/bootstrap` (fresh `transaction_identify` + `dcbprotect`).
2. User submits phone → `POST /connex/api/request-otp` (server calls `login-connex`; OTP sent).
3. UI **automatically switches** to the OTP field (`#otp` in `#connex_otp_step`; button label becomes “Verify OTP”).
4. User submits OTP → `POST /connex/api/confirm-otp` (Laravel):
   - Calls upstream `login-confirm-connex` with `msisdn` + `otp` + `device_type`
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

## Changelog

### v1.1.0

- **Security:** upstream auth, protected-script, and login-connex run on the server; browser only runs `dcbprotect` / `gateway-load`.
- New API routes: `POST {CONNEX_API_PREFIX}/bootstrap`, `POST …/request-otp`.
- Default confirm upstream path: `/login-confirm-connex` (`CONNEX_LOGIN_CONFIRM_ENDPOINT`).

### v1.0.2

- Send `device_type` on login-confirm upstream request.

### v1.0.1

- Fix: Sign In button visible on OTP step.

### v1.0.0

- Initial release.
