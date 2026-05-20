<?php

namespace Torgodly\Connex\Support;

class ConnexScriptConfig
{
    public static function toArray(): array
    {
        $selectors = config('connex.selectors');

        $apiPrefix = trim(config('connex.routes.api_prefix', 'connex/api'), '/');
        $confirmPath = trim(config('connex.routes.confirm_otp', 'confirm-otp'), '/');

        return [
            'baseUrl' => config('connex.base_url'),
            'authLoginUrl' => ConnexUrl::for('auth_login'),
            'protectedScriptUrl' => ConnexUrl::for('protected_script'),
            'loginUrl' => ConnexUrl::for('login'),
            'loginConfirmUrl' => ConnexUrl::for('login_confirm'),
            'confirmOtpUrl' => url($apiPrefix.'/'.$confirmPath),
            'csrfToken' => csrf_token(),
            'authEmail' => config('connex.auth.email'),
            'authPassword' => config('connex.auth.password'),
            'authEmailField' => config('connex.auth.email_field'),
            'authPasswordField' => config('connex.auth.password_field'),
            'tokenJsonPath' => config('connex.auth.token_json_path'),
            'deviceType' => config('connex.device_type'),
            'gatewayLoadTimeoutMs' => config('connex.gateway_load_timeout_ms'),
            'debugLog' => config('connex.debug_log'),
            'selectors' => $selectors,
            'targetedElement' => '#'.($selectors['submit_button'] ?? 'cta_button'),
        ];
    }
}
