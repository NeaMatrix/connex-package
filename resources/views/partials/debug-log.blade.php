@if (config('connex.debug_log'))
    @php
        $logId = config('connex.selectors.log_output', 'log_output');
        $clearId = config('connex.selectors.log_clear', 'log_clear');
    @endphp
    <div class="bg-white rounded shadow-md w-full max-w-2xl flex flex-col max-h-80 mt-4">
        <div class="flex items-center justify-between px-4 py-2 border-b border-slate-200 bg-slate-50 rounded-t">
            <h3 class="text-sm font-semibold text-slate-700">Connex activity log</h3>
            <button type="button" id="{{ $clearId }}" class="text-xs text-indigo-600 hover:text-indigo-800">Clear</button>
        </div>
        <pre id="{{ $logId }}" class="p-4 text-xs text-slate-800 overflow-auto flex-1 font-mono whitespace-pre-wrap break-words m-0"></pre>
    </div>
@endif
