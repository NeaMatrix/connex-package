<?php

namespace Torgodly\Connex\Support;

class ConnexUrl
{
    /**
     * @param  'auth_login'|'protected_script'|'login'|'login_confirm'  $endpoint
     */
    public static function for(string $endpoint): string
    {
        $path = config("connex.endpoints.{$endpoint}");

        if (! is_string($path) || $path === '') {
            throw new \InvalidArgumentException("Unknown Connex endpoint [{$endpoint}]");
        }

        $path = str_starts_with($path, '/') ? $path : '/'.$path;

        return rtrim((string) config('connex.base_url'), '/').$path;
    }
}
