<?php

namespace Torgodly\Connex\Support;

class ConnexUi
{
    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'root' => 'connex-login',
            'card' => 'bg-white p-8 rounded shadow-md w-full max-w-md',
            'title' => 'text-2xl font-bold mb-6 text-center',
            'msisdn_label' => 'block text-sm font-medium text-gray-700',
            'msisdn_input' => 'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
            'otp_hint' => 'text-sm text-slate-600 mb-3 mt-4',
            'otp_label' => 'block text-sm font-medium text-gray-700',
            'otp_input' => 'mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
            'phone_step' => '',
            'otp_step' => 'hidden',
            'submit_wrapper' => 'mt-4',
            'submit_button' => 'w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-400 cursor-not-allowed',
            'submit_button_enabled' => 'w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700',
            'submit_button_disabled' => 'w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white bg-indigo-400 cursor-not-allowed',
            'hidden' => 'hidden',
            'debug_panel' => 'bg-white rounded shadow-md w-full max-w-2xl flex flex-col max-h-80 mt-4',
            'debug_header' => 'flex items-center justify-between px-4 py-2 border-b border-slate-200 bg-slate-50 rounded-t',
            'debug_title' => 'text-sm font-semibold text-slate-700',
            'debug_clear' => 'text-xs text-indigo-600 hover:text-indigo-800',
            'debug_output' => 'p-4 text-xs text-slate-800 overflow-auto flex-1 font-mono whitespace-pre-wrap break-words m-0',
        ];
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    public static function merge(array $overrides = []): array
    {
        return array_merge(
            self::defaults(),
            config('connex.ui.classes', []),
            $overrides
        );
    }

    public static function get(string $key, array $overrides = []): string
    {
        return self::merge($overrides)[$key] ?? '';
    }
}
