@php
    use Torgodly\Connex\Support\ConnexUi;
    $ui = ConnexUi::merge($uiOverrides ?? []);
    $msisdnId = config('connex.selectors.msisdn', 'msisdn');
@endphp
<div>
    <label for="{{ $msisdnId }}" class="{{ $ui['msisdn_label'] }}">{{ $label ?? 'Phone (MSISDN)' }}</label>
    <input id="{{ $msisdnId }}" name="msisdn" type="tel" required class="{{ $ui['msisdn_input'] }}">
</div>
