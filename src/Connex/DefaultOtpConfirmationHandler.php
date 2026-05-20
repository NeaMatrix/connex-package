<?php

namespace Torgodly\Connex\Connex;

use Illuminate\Contracts\Auth\Authenticatable;
use Torgodly\Connex\Contracts\HandlesOtpConfirmation;

class DefaultOtpConfirmationHandler implements HandlesOtpConfirmation
{
    public function handle(Authenticatable $user, array $connexSuccess): array
    {
        return [];
    }
}
