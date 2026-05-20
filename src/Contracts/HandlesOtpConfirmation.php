<?php

namespace Torgodly\Connex\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Host app implements this to create/update the local user from Connex login-confirm success payload.
 *
 * @param  array<string, mixed>  $connexSuccess  Keys e.g. message, subscriber, msisdn, status, operator, expiration_date
 * @return array<string, mixed>  Merged into the JSON API response for web/mobile (e.g. custom fields, redirect hints)
 */
interface HandlesOtpConfirmation
{
    public function handle(Authenticatable $user, array $connexSuccess): array;
}
