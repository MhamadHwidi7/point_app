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
        $data = $request->query();

        $validatedData = Validator::make($data, [
            'device_token' => 'required|string',
            'verification_code' => 'required|digits:4'
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'message' => $validatedData->errors()->first()
            ], 400);
        }

        $user = User::where('device_token', $data['device_token'])->first();

        if ($user && $user->verification_code == $data['verification_code']) {
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
            Log::error('Failed to send welcome notification: ' . $e->getMessage());
        }
    }
}
