<?php

namespace Torgodly\Connex\Services;

use Torgodly\Connex\Actions\ConfirmOtpUpstream;
use Torgodly\Connex\Actions\IssueMobileAuthToken;
use Torgodly\Connex\Actions\SyncConnexSubscriberUser;
use Torgodly\Connex\Contracts\HandlesOtpConfirmation;

class OtpConfirmationService
{
    public function __construct(
        protected ConfirmOtpUpstream $confirmOtpUpstream,
        protected SyncConnexSubscriberUser $syncUser,
        protected IssueMobileAuthToken $issueToken,
        protected HandlesOtpConfirmation $otpHandler,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function confirm(string $msisdn, string $otp): array
    {
        $upstream = $this->confirmOtpUpstream->confirm($msisdn, $otp);

        if (($upstream['messageCode'] ?? null) !== '00' || ! isset($upstream['success'])) {
            return [
                'messageCode' => $upstream['messageCode'] ?? 'error',
                'failed' => $upstream['failed'] ?? $upstream,
            ];
        }

        $connexSuccess = $upstream['success'];
        $user = $this->syncUser->sync($connexSuccess);
        $tokenPayload = $this->issueToken->forUser($user);
        $custom = $this->otpHandler->handle($user, $connexSuccess);

        return array_merge([
            'messageCode' => '00',
            'success' => [
                'message' => $connexSuccess['message'] ?? 'OTP confirmed',
                'connex' => $connexSuccess,
            ],
            'auth' => array_merge($tokenPayload, [
                'user' => $this->formatUser($user),
            ]),
        ], $custom);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatUser(\Illuminate\Contracts\Auth\Authenticatable $user): array
    {
        return [
            'id' => $user->getAuthIdentifier(),
            'msisdn' => $user->msisdn ?? null,
            'subscriber' => $user->subscriber ?? null,
            'status' => $user->status ?? null,
            'operator' => $user->operator ?? null,
            'expiration_date' => $user->expiration_date ?? null,
        ];
    }
}
