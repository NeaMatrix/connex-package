<?php

namespace Torgodly\Connex\Support;

class ConnexScriptConfig
{
    public static function toArray(): array
    {
        $selectors = config('connex.selectors');
        $apiPrefix = trim(config('connex.routes.api_prefix', 'connex/api'), '/');
        $submitId = $selectors['submit_button'] ?? 'cta_button';

        return [
            'bootstrapUrl' => url($apiPrefix.'/'.trim(config('connex.routes.bootstrap', 'bootstrap'), '/')),
            'requestOtpUrl' => url($apiPrefix.'/'.trim(config('connex.routes.request_otp', 'request-otp'), '/')),
            'confirmOtpUrl' => url($apiPrefix.'/'.trim(config('connex.routes.confirm_otp', 'confirm-otp'), '/')),
            'csrfToken' => csrf_token(),
            'deviceType' => config('connex.device_type'),
            'gatewayLoadTimeoutMs' => config('connex.gateway_load_timeout_ms'),
            'debugLog' => config('connex.debug_log'),
            'selectors' => $selectors,
            'targetedElement' => '#'.$submitId,
        ];
    }
}
