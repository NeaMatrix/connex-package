<?php

namespace Torgodly\Connex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Torgodly\Connex\Services\ConnexLoginService;
use Torgodly\Connex\Services\OtpConfirmationService;

class ConnexAuthController extends Controller
{
    public function bootstrap(Request $request, ConnexLoginService $loginService): JsonResponse
    {
        $data = $request->validate([
            'targeted_element' => ['nullable', 'string'],
        ]);

        $submitId = config('connex.selectors.submit_button', 'cta_button');
        $targeted = $data['targeted_element'] ?? '#'.$submitId;

        $result = $loginService->bootstrap($targeted);

        $status = ($result['messageCode'] ?? null) === '00' ? 200 : 422;

        return response()->json($result, $status);
    }

    public function requestOtp(Request $request, ConnexLoginService $loginService): JsonResponse
    {
        $data = $request->validate([
            'msisdn' => ['required', 'string'],
            'transaction_identify' => ['required', 'string'],
        ]);

        $result = $loginService->requestOtp($data['msisdn'], $data['transaction_identify']);

        $status = ($result['messageCode'] ?? null) === '00' ? 200 : 422;

        return response()->json($result, $status);
    }

    public function confirmOtp(Request $request, OtpConfirmationService $service): JsonResponse
    {
        $data = $request->validate([
            'msisdn' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $result = $service->confirm($data['msisdn'], $data['otp']);

        $status = ($result['messageCode'] ?? null) === '00' ? 200 : 422;

        return response()->json($result, $status);
    }
}
