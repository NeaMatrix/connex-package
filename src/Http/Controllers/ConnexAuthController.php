<?php

namespace Torgodly\Connex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Torgodly\Connex\Services\OtpConfirmationService;

class ConnexAuthController extends Controller
{
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
