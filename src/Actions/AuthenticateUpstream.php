<?php

namespace Torgodly\Connex\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Torgodly\Connex\Support\ConnexUrl;

class AuthenticateUpstream
{
    public function token(): string
    {
        $token = Cache::remember('connex_upstream_token', now()->addMinutes(50), function () {
            $url = ConnexUrl::for('auth_login');
            $payload = [
                config('connex.auth.email_field') => config('connex.auth.email'),
                config('connex.auth.password_field') => config('connex.auth.password'),
            ];

            $response = config('connex.auth.body_format', 'json') === 'form-urlencoded'
                ? Http::asForm()->post($url, $payload)
                : Http::asJson()->post($url, $payload);

            $response->throw();

            $json = $response->json();
            if (($json['messageCode'] ?? null) !== '00') {
                throw new \RuntimeException('Connex auth-login rejected: '.($json['failed']['message'] ?? 'unknown'));
            }

            $accessToken = data_get($json, config('connex.auth.token_json_path', 'data.access_token'));
            if (! is_string($accessToken) || $accessToken === '') {
                throw new \RuntimeException('Connex auth-login: access token missing');
            }

            return $accessToken;
        });

        return $token;
    }
}
