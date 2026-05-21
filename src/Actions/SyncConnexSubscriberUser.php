<?php

namespace Torgodly\Connex\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncConnexSubscriberUser
{
    /**
     * @param  array<string, mixed>  $connexSuccess
     */
    public function sync(array $connexSuccess): Authenticatable
    {
        $modelClass = config('connex.user_model');
        $msisdn = (string) ($connexSuccess['msisdn'] ?? '');

        if ($msisdn === '') {
            throw new \InvalidArgumentException('Connex success payload missing msisdn');
        }

        $email = $this->emailForMsisdn($msisdn);

        $attributes = [
            'name' => $msisdn,
            'email' => $email,
            'msisdn' => $msisdn,
            'subscriber' => isset($connexSuccess['subscriber'])
                ? (string) $connexSuccess['subscriber']
                : null,
            'status' => $connexSuccess['status'] ?? null,
            'operator' => $connexSuccess['operator'] ?? null,
            'expiration_date' => $connexSuccess['expiration_date'] ?? null,
        ];

        $user = $modelClass::query()
            ->where('msisdn', $msisdn)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->fill($attributes);
            $user->save();
        } else {
            $user = $modelClass::query()->create(array_merge($attributes, [
                'password' => Hash::make(Str::random(32)),
            ]));
        }

        return $user;
    }

    protected function emailForMsisdn(string $msisdn): string
    {
        $domain = (string) config('connex.user.email_domain', 'connex.local');

        return $msisdn.'@'.$domain;
    }
}
