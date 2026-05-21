<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connex Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center p-4 gap-4">
    {{-- Example layout: copy this file to resources/views/ and edit classes/markup freely --}}
    <x-connex-login class="w-full max-w-md flex flex-col gap-4" data-connex-hidden-class="hidden">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-center text-slate-900">Login to Connex</h1>

            @include('connex::partials.hidden-fields')

            <div id="{{ config('connex.selectors.phone_step', 'connex_phone_step') }}">
                <label for="{{ config('connex.selectors.msisdn', 'msisdn') }}" class="block text-sm font-medium text-gray-700">
                    Phone (MSISDN)
                </label>
                <input
                    id="{{ config('connex.selectors.msisdn', 'msisdn') }}"
                    name="msisdn"
                    type="tel"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
            </div>

            <div id="{{ config('connex.selectors.otp_step', 'connex_otp_step') }}" class="hidden">
                <p id="{{ config('connex.selectors.otp_hint', 'connex_otp_hint') }}" class="text-sm text-slate-600 mb-3 mt-4">
                    Enter the OTP sent to your phone.
                </p>
                <label for="{{ config('connex.selectors.otp', 'otp') }}" class="block text-sm font-medium text-gray-700">
                    OTP code
                </label>
                <input
                    id="{{ config('connex.selectors.otp', 'otp') }}"
                    name="otp"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="8"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
            </div>

            <div class="mt-4">
                <button
                    id="{{ config('connex.selectors.submit_button', 'cta_button') }}"
                    type="button"
                    disabled
                    data-connex-label-sign-in="Sign In"
                    data-connex-label-loading="Loading…"
                    data-connex-label-signing-in="Sending OTP…"
                    data-connex-label-verify-otp="Verify OTP"
                    data-connex-enabled-class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                    data-connex-disabled-class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-400 cursor-not-allowed"
                    class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-400 cursor-not-allowed"
                >
                    Loading…
                </button>
            </div>
        </div>
    </x-connex-login>
</body>
</html>
