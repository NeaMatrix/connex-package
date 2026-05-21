@if (config('connex.debug_log'))
    @php
        use Torgodly\Connex\Support\ConnexUi;
        $ui = ConnexUi::merge($uiOverrides ?? []);
        $logId = config('connex.selectors.log_output', 'log_output');
        $clearId = config('connex.selectors.log_clear', 'log_clear');
    @endphp
    <div class="{{ $ui['debug_panel'] }}">
        <div class="{{ $ui['debug_header'] }}">
            <h3 class="{{ $ui['debug_title'] }}">Connex activity log</h3>
            <button type="button" id="{{ $clearId }}" class="{{ $ui['debug_clear'] }}">Clear</button>
        </div>
        <pre id="{{ $logId }}" class="{{ $ui['debug_output'] }}"></pre>
    </div>
@endif
