@props([
    'title' => 'Login',
    'classes' => [],
])

@php
    use Torgodly\Connex\Support\ConnexUi;
    $ui = ConnexUi::merge(is_array($classes) ? $classes : []);
    $submitId = config('connex.selectors.submit_button', 'cta_button');
    $phoneStepId = config('connex.selectors.phone_step', 'connex_phone_step');
    $otpStepId = config('connex.selectors.otp_step', 'connex_otp_step');
    $hiddenClass = $ui['hidden'] ?: 'hidden';
@endphp

<div {{ $attributes->class($ui['root']) }}>
    @isset($form)
        {{ $form }}
    @else
        <div @class([$ui['card']])>
            <h2 @class([$ui['title']])>{{ $title }}</h2>

            @include('connex::partials.hidden-fields')

            <div id="{{ $phoneStepId }}" @if ($ui['phone_step']) class="{{ $ui['phone_step'] }}" @endif>
                @include('connex::partials.msisdn-field', ['uiOverrides' => $ui])
            </div>

            <div id="{{ $otpStepId }}" class="{{ trim($hiddenClass.' '.($ui['otp_step'] ?? '')) }}">
                @include('connex::partials.otp-field', ['uiOverrides' => $ui])
            </div>

            <div @class([$ui['submit_wrapper']])>
                @include('connex::partials.submit-button', ['uiOverrides' => $ui])
            </div>
        </div>
    @endisset

    @isset($after)
        {{ $after }}
    @endisset

    @isset($debug)
        {{ $debug }}
    @else
        @include('connex::partials.debug-log', ['uiOverrides' => $ui])
    @endif
</div>

@include('connex::partials.scripts')
