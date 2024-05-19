<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class UserSignupController extends Controller
{
    private $messaging;

    public function __construct(Factory $firebase)
    {
        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
    }

    public function register(Request $request)
    {
        $data = $request->all();

        $data['card_number'] = str_replace(' ', '', $data['card_number']);

        $validatedData = Validator::make($data, [
            'name' => 'required|string|max:255|unique:users',
            'account_number' => 'required|string|size:21|unique:users',
            'password' => 'required|string|min:8',
            'card_number' => 'required|string|size:24|unique:users',
            'card_passcode' => 'required|digits:4',
            'device_token' => 'sometimes|nullable|string'
        ], [
            'name.required' => 'الاسم مطلوب.',
            'name.string' => 'يجب أن يكون الاسم نصيًا.',
            'name.max' => 'يجب ألا يزيد الاسم عن 255 حرفًا.',
            'name.unique' => 'الاسم موجود مسبقًا، الرجاء اختيار اسم آخر.',
            'account_number.required' => 'رقم الحساب مطلوب.',
            'account_number.string' => 'يجب أن يكون رقم الحساب نصيًا.',
            'account_number.size' => 'يجب أن يكون رقم الحساب مكون من 21 رقمًا.',
            'account_number.unique' => 'رقم الحساب موجود مسبقًا، الرجاء اختيار رقم آخر.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.string' => 'يجب أن تكون كلمة المرور نصية.',
            'password.min' => 'يجب أن تكون كلمة المرور على الأقل مكونة من 8 أحرف.',
            'card_number.required' => 'رقم البطاقة مطلوب.',
            'card_number.string' => 'يجب أن يكون رقم البطاقة نصيًا.',
            'card_number.size' => 'يجب أن يكون رقم البطاقة مكون من 24 حرفًا بدون فراغات.',
            'card_number.unique' => 'رقم البطاقة موجود مسبقًا، الرجاء اختيار رقم آخر.',
            'card_passcode.required' => 'رمز البطاقة مطلوب.',
            'card_passcode.digits' => 'يجب أن يكون رمز البطاقة مكون من 4 أرقام.',
            'device_token.sometimes' => 'رمز الجهاز مطلوب أحيانًا.',
            'device_token.nullable' => 'يمكن ترك رمز الجهاز فارغًا.',
            'device_token.string' => 'يجب أن يكون رمز الجهاز نصيًا.'
        ]);

        if ($validatedData->fails()) {
            return response()->json(['message' => $validatedData->errors()->first()], 400);
        }

        $user = new User([
            'name' => $data['name'],
            'account_number' => $data['account_number'],
            'password' => Hash::make($data['password']),
            'card_number' => $data['card_number'],
            'card_passcode' => $data['card_passcode'],
            'device_token' => $data['device_token'],
            'role' => 'user'
        ]);

        $verificationCode = rand(1000, 9999);
        $user->verification_code = $verificationCode;

        if ($user->save()) {
            $this->sendWelcomeNotification($user->device_token); // Send welcome notification here
            $token = JWTAuth::fromUser($user);

            $formattedCardNumber = implode(' ', str_split($user->card_number, 4));

            return response()->json([
                'status' => 200,
                'success' => true,
                'token' => $token,
                'device_token' => $user->device_token,
                'account_number' => $user->account_number,
                'card_number' => $user->card_number,
                'message' => 'تم التسجيل بنجاح! يرجى إدخال رمز التحقق.',
                'verification_code' => $verificationCode
            ]);
        } else {
            return response()->json([
                'message' => 'حدث خطأ ما، لم يتم حفظ المستخدم.'
            ], 400);
        }
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
