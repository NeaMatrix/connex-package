@php
    use Torgodly\Connex\Support\ConnexUi;
    $ui = ConnexUi::merge($uiOverrides ?? []);
    $otpId = config('connex.selectors.otp', 'otp');
    $hintId = config('connex.selectors.otp_hint', 'connex_otp_hint');
@endphp
<p id="{{ $hintId }}" class="{{ $ui['otp_hint'] }}">{{ $hint ?? 'Enter the OTP sent to your phone.' }}</p>
<div>
    <label for="{{ $otpId }}" class="{{ $ui['otp_label'] }}">{{ $label ?? 'OTP code' }}</label>
    <input id="{{ $otpId }}" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="8"
        class="{{ $ui['otp_input'] }}">
</div>
