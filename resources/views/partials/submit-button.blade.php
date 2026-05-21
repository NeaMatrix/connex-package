@php
    use Torgodly\Connex\Support\ConnexUi;
    $ui = ConnexUi::merge($uiOverrides ?? []);
    $submitId = config('connex.selectors.submit_button', 'cta_button');
@endphp
<button id="{{ $submitId }}" type="button" disabled
    data-connex-label-sign-in="{{ $signInLabel ?? 'Sign In' }}"
    data-connex-label-loading="{{ $loadingLabel ?? 'Loading…' }}"
    data-connex-label-signing-in="{{ $signingInLabel ?? 'Sending OTP…' }}"
    data-connex-label-verify-otp="{{ $verifyOtpLabel ?? 'Verify OTP' }}"
    data-connex-enabled-class="{{ $ui['submit_button_enabled'] }}"
    data-connex-disabled-class="{{ $ui['submit_button_disabled'] }}"
    class="{{ $ui['submit_button'] }}">
    {{ $loadingLabel ?? 'Loading…' }}
</button>
