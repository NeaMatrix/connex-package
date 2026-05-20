<?php

namespace Torgodly\Connex\Actions;

use Illuminate\Contracts\Auth\Authenticatable;

class IssueMobileAuthToken
{
    /**
     * @return array{token: string|null, token_type: string, expires_at: string|null}
     */
    public function forUser(Authenticatable $user): array
    {
        $driver = config('connex.mobile.token_driver', 'sanctum');
        $name = config('connex.mobile.token_name', 'connex-mobile');

        if ($driver === 'sanctum' && method_exists($user, 'createToken')) {
            $token = $user->createToken($name);

            return [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => null,
            ];
        }

        if ($driver === 'plain' && $this->supportsPlainToken($user)) {
            $plain = bin2hex(random_bytes(32));
            $user->forceFill(['api_token' => hash('sha256', $plain)])->save();

            return [
                'token' => $plain,
                'token_type' => 'Bearer',
                'expires_at' => null,
            ];
        }

        return [
            'token' => null,
            'token_type' => 'Bearer',
            'expires_at' => null,
        ];
    }

    protected function supportsPlainToken(Authenticatable $user): bool
    {
        return in_array('api_token', $user->getFillable(), true)
            || array_key_exists('api_token', $user->getAttributes());
    }
}
