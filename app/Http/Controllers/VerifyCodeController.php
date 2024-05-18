<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyCodeController extends Controller
{
    private $messaging;

    public function __construct(Factory $firebase)
    {
        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
    }

    public function verifyCode(Request $request)
    {
        $data = $request->all(); // Get all request data

        Log::info('Received verification request', ['data' => $data]);

        $validatedData = Validator::make($data, [
            'device_token' => 'required_without:user_id|string',
            'user_id' => 'required_without:device_token|integer',
            'verification_code' => 'required|digits:4'
        ]);

        if ($validatedData->fails()) {
            Log::error('Validation failed', ['errors' => $validatedData->errors()->first()]);
            return response()->json([
                'message' => $validatedData->errors()->first()
            ], 400);
        }

        $userQuery = User::query();
        if (!empty($data['device_token'])) {
            $userQuery->where('device_token', $data['device_token']);
        }
        if (!empty($data['user_id'])) {
            $userQuery->where('id', $data['user_id']);
        }

        $user = $userQuery->first();

        if (!$user) {
            Log::error('User not found', ['query' => $data]);
            return response()->json([
                'message' => 'المستخدم غير موجود.'
            ], 400);
        }

        Log::info('User found', ['user' => $user]);

        if ($user->verification_code == $data['verification_code']) {
            Log::info('Verification code matched', ['user' => $user]);
            $user->verification_code = null;
            $user->save();

            $token = JWTAuth::fromUser($user);

            $this->sendWelcomeNotification($user->device_token);

            return response()->json([
                'status' => 200,
                'success' => true,
                'token' => $token,
                'account_number' => $user->account_number,
                'message' => 'تم التحقق بنجاح! مرحبًا بك في بنك الراجحي.'
            ]);
        } else {
            Log::error('Verification code mismatch', [
                'device_token' => $data['device_token'] ?? null,
                'provided_code' => $data['verification_code'],
                'stored_code' => $user->verification_code
            ]);
            return response()->json([
                'message' => 'رمز التحقق غير صحيح.'
            ], 400);
        }
    }

    protected function sendWelcomeNotification($deviceToken)
    {
        if (!$deviceToken) {
            return;
        }

        $title = 'مرحبًا بك في بنك الراجحي!';
        $body = 'شكرًا لتسجيلك معنا. نحن متحمسون لانضمامك إلى خدماتنا ونتطلع إلى تقديم أفضل الخدمات لك.';

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create($title, $body));

        try {
            $this->messaging->send($message);
        } catch (\Throwable $e) {
            Log::error('Failed to send welcome notification', ['error' => $e->getMessage()]);
        }
    }
}
