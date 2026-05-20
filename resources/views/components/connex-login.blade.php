@props([
    'title' => 'Login',
])

@php
    $submitId = config('connex.selectors.submit_button', 'cta_button');
    $msisdnId = config('connex.selectors.msisdn', 'msisdn');
    $otpId = config('connex.selectors.otp', 'otp');
    $phoneStepId = config('connex.selectors.phone_step', 'connex_phone_step');
    $otpStepId = config('connex.selectors.otp_step', 'connex_otp_step');
@endphp

<div {{ $attributes->merge(['class' => 'connex-login']) }}>
    @isset($form)
        {{ $form }}
    @else
        <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">{{ $title }}</h2>

            @include('connex::partials.hidden-fields')

            <div id="{{ $phoneStepId }}">
                <div>
                    <label for="{{ $msisdnId }}" class="block text-sm font-medium text-gray-700">Phone (MSISDN)</label>
                    <input id="{{ $msisdnId }}" name="msisdn" type="tel" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div id="{{ $otpStepId }}" class="hidden">
                <p id="connex_otp_hint" class="text-sm text-slate-600 mb-3 mt-4">Enter the OTP sent to your phone.</p>
                <div>
                    <label for="{{ $otpId }}" class="block text-sm font-medium text-gray-700">OTP code</label>
                    <input id="{{ $otpId }}" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="8"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="mt-4">
                <button id="{{ $submitId }}" type="button" disabled
                    data-connex-label-sign-in="Sign In"
                    data-connex-label-loading="Loading…"
                    data-connex-label-signing-in="Sending OTP…"
                    data-connex-label-verify-otp="Verify OTP"
                    data-connex-enabled-class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                    data-connex-disabled-class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-400 cursor-not-allowed"
                    class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-400 cursor-not-allowed">
                    Loading…
                </button>
            </div>
        </div>
    @endisset

    @isset($after)
        {{ $after }}
    @endisset

    @if (! isset($debug))
        @include('connex::partials.debug-log')
    @else
        {{ $debug }}
    @endif
</div>

@include('connex::partials.scripts')
