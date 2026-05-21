<?php

namespace Torgodly\Connex\Actions;

use Illuminate\Support\Facades\Http;
use Torgodly\Connex\Support\ConnexUrl;

class RequestOtpUpstream
{
    public function __construct(
        protected AuthenticateUpstream $auth,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function request(string $msisdn, string $transactionIdentify): array
    {
        $response = Http::withToken($this->auth->token())
            ->asForm()
            ->post(ConnexUrl::for('login'), [
                'msisdn' => $msisdn,
                'transaction_identify' => $transactionIdentify,
                'device_type' => config('connex.device_type', 'web'),
            ]);

        $data = $response->json() ?? [];

        if (! $response->successful()) {
            return is_array($data) ? $data : ['message' => $response->body()];
        }

        return $data;
    }
}
