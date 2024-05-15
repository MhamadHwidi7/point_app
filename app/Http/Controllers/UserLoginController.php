<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class UserLoginController extends Controller
{
    private $messaging;

    public function __construct(Factory $firebase)
    {
        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string', 
            'password' => 'required|string',
            'device_token' => 'sometimes|string'
        ]);
    
        $user = User::where('name', $validatedData['name'])->first();
        
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['message' => 'غير مصرح'], 401); // Unauthorized
        }

        if (isset($validatedData['device_token'])) {
            $user->device_token = $validatedData['device_token'];
            $user->save();
        }

        // Generate a random 4-digit code for verification
        $verificationCode = rand(1000, 9999);
        $user->verification_code = $verificationCode;
        $user->save();

        // Send the verification code notification
        $this->sendVerificationCode($user->device_token, $verificationCode);
    
        $token = JWTAuth::fromUser($user);
    
        return response()->json([
            'message' => 'تم الدخول بنجاح! يرجى إدخال رمز التحقق.',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'account_number' => $user->account_number,
            'card_number' => $user->card_number,
            'token' => $token,
            'device_token' => $user->device_token,
            'verification_code' => $verificationCode // Include the verification code in the response
        ]);
    }

    protected function sendVerificationCode($deviceToken, $verificationCode)
    {
        if (!$deviceToken) {
            return;
        }

        $title = 'رمز التحقق من بنك الراجحي';
        $body = "رمز التحقق الخاص بك هو: $verificationCode";

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create($title, $body));

        try {
            $this->messaging->send($message);
        } catch (\Throwable $e) {
            Log::error('Failed to send verification code: ' . $e->getMessage());
        }
    }
}
