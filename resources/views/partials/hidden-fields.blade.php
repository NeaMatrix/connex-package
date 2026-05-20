@php
    $txId = config('connex.selectors.transaction_identify', 'transaction_identify');
    $deviceName = config('connex.selectors.device_type', 'device_type');
@endphp
<input type="hidden" name="{{ $deviceName }}" id="{{ $deviceName }}" value="{{ config('connex.device_type', 'web') }}">
<input type="hidden" name="transaction_identify" id="{{ $txId }}" value="">
