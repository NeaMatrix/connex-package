<?php

namespace Torgodly\Connex\Services;

use Torgodly\Connex\Actions\LoadProtectedScript;
use Torgodly\Connex\Actions\RequestOtpUpstream;

class ConnexLoginService
{
    public function __construct(
        protected LoadProtectedScript $loadProtectedScript,
        protected RequestOtpUpstream $requestOtpUpstream,
    ) {}

    /**
     * Server-side auth-login + protected-script. Returns only browser-safe fields.
     *
     * @return array<string, mixed>
     */
    public function bootstrap(string $targetedElement): array
    {
        $upstream = $this->loadProtectedScript->fetch($targetedElement);

        if (($upstream['messageCode'] ?? null) !== '00' || ! isset($upstream['success'])) {
            return [
                'messageCode' => $upstream['messageCode'] ?? 'error',
                'failed' => $upstream['failed'] ?? $upstream,
            ];
        }

        $success = $upstream['success'];

        if (empty($success['transaction_identify']) || empty($success['dcbprotect'])) {
            return [
                'messageCode' => 'error',
                'failed' => ['message' => 'protected-script missing transaction_identify or dcbprotect'],
            ];
        }

        return [
            'messageCode' => '00',
            'transaction_identify' => $success['transaction_identify'],
            'dcbprotect' => $success['dcbprotect'],
            'message' => $success['message'] ?? 'DCB Protected script',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function requestOtp(string $msisdn, string $transactionIdentify): array
    {
        return $this->requestOtpUpstream->request($msisdn, $transactionIdentify);
    }
}
