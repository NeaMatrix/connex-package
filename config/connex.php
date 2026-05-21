<?php

return [

    'base_url' => rtrim(env('CONNEX_BASE_URL', 'https://jobassistant.mooo.com'), '/'),

    /*
    | Upstream path segments appended to CONNEX_BASE_URL
    */
    'endpoints' => [
        'auth_login' => env('CONNEX_AUTH_LOGIN_ENDPOINT', env('CONNEX_AUTH_LOGIN_PATH', '/auth-login')),
        'protected_script' => env('CONNEX_PROTECTED_SCRIPT_ENDPOINT', env('CONNEX_PROTECTED_SCRIPT_PATH', '/protected-script')),
        'login' => env('CONNEX_LOGIN_CONNEX_ENDPOINT', env('CONNEX_LOGIN_CONNEX_PATH', env('CONNEX_LOGIN_PATH', '/login-connex'))),
        'login_confirm' => env('CONNEX_LOGIN_CONFIRM_ENDPOINT', env('CONNEX_LOGIN_CONFIRM_PATH', '/login-confirm-connex')),
    ],

    'auth' => [
        'email' => env('CONNEX_AUTH_EMAIL'),
        'password' => env('CONNEX_AUTH_PASSWORD'),
        'email_field' => env('CONNEX_AUTH_EMAIL_FIELD', 'email'),
        'password_field' => env('CONNEX_AUTH_PASSWORD_FIELD', 'password'),
        'token_json_path' => env('CONNEX_AUTH_TOKEN_JSON_PATH', 'data.access_token'),
        'body_format' => env('CONNEX_AUTH_BODY_FORMAT', 'json'),
    ],

    'user_model' => env('CONNEX_USER_MODEL', \App\Models\User::class),

    'user' => [
        'email_domain' => env('CONNEX_USER_EMAIL_DOMAIN', 'connex.local'),
    ],

    'otp_handler' => env('CONNEX_OTP_HANDLER', \Torgodly\Connex\Connex\DefaultOtpConfirmationHandler::class),

    'mobile' => [
        'token_driver' => env('CONNEX_MOBILE_TOKEN_DRIVER', 'sanctum'),
        'token_name' => env('CONNEX_MOBILE_TOKEN_NAME', 'connex-mobile'),
    ],

    'device_type' => env('CONNEX_DEVICE_TYPE', 'web'),

    'gateway_load_timeout_ms' => (int) env('CONNEX_GATEWAY_LOAD_TIMEOUT_MS', 15000),

    'debug_log' => (bool) env('CONNEX_DEBUG_LOG', true),

    'selectors' => [
        'msisdn' => env('CONNEX_MSISDN_INPUT_ID', 'msisdn'),
        'otp' => env('CONNEX_OTP_INPUT_ID', 'otp'),
        'submit_button' => env('CONNEX_SUBMIT_BUTTON_ID', 'cta_button'),
        'transaction_identify' => env('CONNEX_TRANSACTION_INPUT_ID', 'transaction_identify'),
        'device_type' => env('CONNEX_DEVICE_TYPE_INPUT_NAME', 'device_type'),
        'phone_step' => env('CONNEX_PHONE_STEP_ID', 'connex_phone_step'),
        'otp_step' => env('CONNEX_OTP_STEP_ID', 'connex_otp_step'),
        'log_output' => env('CONNEX_LOG_OUTPUT_ID', 'log_output'),
        'log_clear' => env('CONNEX_LOG_CLEAR_ID', 'log_clear'),
        'otp_hint' => env('CONNEX_OTP_HINT_ID', 'connex_otp_hint'),
    ],

    'routes' => [
        'login' => env('CONNEX_WEB_LOGIN_PATH', '/connex/login'),
        'login_confirm' => env('CONNEX_WEB_LOGIN_CONFIRM_PATH', '/connex/login-confirm'),
        'api_prefix' => env('CONNEX_API_PREFIX', 'connex/api'),
        'bootstrap' => env('CONNEX_API_BOOTSTRAP_PATH', 'bootstrap'),
        'request_otp' => env('CONNEX_API_REQUEST_OTP_PATH', 'request-otp'),
        'confirm_otp' => env('CONNEX_API_CONFIRM_OTP_PATH', 'confirm-otp'),
    ],

    'views' => [
        'login' => env('CONNEX_LOGIN_VIEW', 'connex::login'),
        'login_confirm' => env('CONNEX_LOGIN_CONFIRM_VIEW', 'connex::login-confirm'),
    ],

];
