<?php

namespace Torgodly\Connex\Actions;

use Illuminate\Support\Facades\Http;
use Torgodly\Connex\Support\ConnexUrl;

class ConfirmOtpUpstream
{
    public function __construct(
        protected AuthenticateUpstream $auth,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function confirm(string $msisdn, string $otp): array
    {
        $url = ConnexUrl::for('login_confirm');

        $response = Http::withToken($this->auth->token())
            ->asForm()
            ->post($url, [
                'msisdn' => $msisdn,
                'otp' => $otp,
                'device_type' => config('connex.device_type', 'web'),
            ]);

        $data = $response->json() ?? [];

        if (! $response->successful()) {
            return is_array($data) ? $data : ['message' => $response->body()];
        }

        return $data;
    }
}
