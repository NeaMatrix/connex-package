{{--
    Wrapper only: put your full HTML design in the slot (classes, layout, markup).
    Required: correct element IDs from config('connex.selectors') — see README.
    Optional on this element: data-connex-hidden-class="hidden" (class used to hide phone/otp steps)
--}}
<div {{ $attributes->merge(['data-connex-login-root' => '']) }}>
    {{ $slot }}

    @isset($after)
        {{ $after }}
    @endisset

    @isset($debug)
        {{ $debug }}
    @elseif (config('connex.debug_log') && ! ($hideDebug ?? false))
        @include('connex::partials.debug-log')
    @endif
</div>

@include('connex::partials.scripts')
