<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\WhatsAppService;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OtpController extends BaseApiController
{
    /**
     * Request OTP via WhatsApp
     */
    public function request(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^[0-9+\-\s()]+$/|max:20',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Nomor telepon tidak valid');
        }

        // Normalize phone number (remove spaces, dashes, etc.)
        $phoneNumber = preg_replace('/[^0-9+]/', '', $request->phone_number);

        // Generate OTP
        $otp = OtpVerification::generateCode($phoneNumber);

        // Send OTP via WhatsApp Gateway (direct, no queue)
        $appName = config('app.name', 'indonesiamarathon.com');
        $message = "Kode OTP {$appName}: {$otp}\nBerlaku 10 menit. Jangan bagikan ke siapa pun.";
        try {
            $wa = new WhatsAppService();
            $wa->sendMessage($phoneNumber, $message);
        } catch (\Throwable $e) {
            // Ignore downstream errors to avoid leaking info; OTP remains valid
        }

        // For development, return OTP in response (REMOVE IN PRODUCTION)
        $responseData = null;
        if (config('app.debug')) {
            $responseData = [
                'debug' => [
                    'otp' => $otp,
                    'phone' => $phoneNumber,
                ],
            ];
        }

        return $this->successResponse(
            $responseData,
            'OTP berhasil dikirim. Check WhatsApp Anda.'
        );
    }

    /**
     * Verify OTP and create/login user
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^[0-9+\-\s()]+$/|max:20',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Data tidak valid');
        }

        // Normalize phone number
        $phoneNumber = preg_replace('/[^0-9+]/', '', $request->phone_number);

        // Find OTP record
        $otpRecord = OtpVerification::where('phone_number', $phoneNumber)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        // Verify OTP
        if (!$otpRecord || !Hash::check($request->code, $otpRecord->code)) {
            return $this->errorResponse(
                'OTP tidak valid atau sudah kedaluwarsa',
                401
            );
        }

        // Mark OTP as used
        $otpRecord->markAsUsed();

        // Get or create user
        $user = User::firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'name' => null, // Can be updated later
                'email' => null,
            ]
        );

        // Assign default EO role if available
        if (method_exists($user, 'hasRole') && method_exists($user, 'assignRole')) {
            if (!$user->hasRole('EO')) {
                try {
                    $user->assignRole('EO');
                } catch (\Throwable $e) {
                    // role package not installed or role not found; ignore
                }
            }
        }

        // Create Sanctum token
        $token = $user->createToken('nuxt-frontend')->plainTextToken;

        return $this->successResponse(
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'Login berhasil'
        );
    }
}

