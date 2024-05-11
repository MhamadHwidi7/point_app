<?php
// namespace App\Http\Controllers;
// use Illuminate\Http\Request;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Validator;
// use Tymon\JWTAuth\Facades\JWTAuth;
// use Kreait\Firebase\Factory;
// use Kreait\Firebase\Messaging\CloudMessage;
// use Kreait\Firebase\Messaging\Notification;

// class UserSignupController extends Controller
// {
//     private $messaging;

//     public function __construct(Factory $firebase)
//     {
//         $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
//         $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
//     }
//     public function register(Request $request)
//     {
//         $data = $request->all();
//         $validatedData = Validator::make($data, [
//             'name' => 'required|string|max:255|unique:users',
//             'account_number' => 'required|string|max:25|unique:users',
//             'password' => 'required|string|min:8',
//             'card_number' => 'required|string|max:25|unique:users', 
//             'card_passcode' => 'required|digits:4',  
//             'device_token' => 'sometimes|string' 
//         ]);
    
//         if ($validatedData->fails()) {
//             return response()->json([
//                 'message' => $validatedData->errors()->first() // Returns the first validation error message in Arabic
//             ], 400);
//         }
    
//         $user = new User;
//         $user->name = $data['name'];
//         $user->account_number = $data['account_number'];
//         $user->password = Hash::make($data['password']);
//         $user->card_number = $data['card_number']; 
//         $user->card_passcode = $data['card_passcode']; 
//         $user->device_token = $data['device_token'] ?? null;
//         $user->role = 'user';
    
//         if ($user->save()) {
//             $this->sendWelcomeNotification($user->device_token);
//             $token = JWTAuth::fromUser($user);
//             return response()->json([
//                 'status' => 200,
//                 'success' => true,
//                 'token' => $token,
//                 'account_number' => $user->account_number, // Include account number in the response
//                 'message' => 'تم التسجيل بنجاح!' // Your registration has been successfully
//             ]);
//         } else {
//             return response()->json([
//                 'message' => 'حدث خطأ ما، لم يتم حفظ المستخدم.' // Something went wrong, unable to save the user.
//             ], 400);
//         }
//     }
    

//     protected function sendWelcomeNotification($deviceToken)
//     {
//         if (!$deviceToken) {
//             return;
//         }
//             $title = 'مرحبًا بك في بنك الراجحي!';
//         $body = 'شكرًا لتسجيلك معنا. نحن متحمسون لانضمامك إلى خدماتنا ونتطلع إلى تقديم أفضل الخدمات لك.';
    
//         $message = CloudMessage::withTarget('token', $deviceToken)
//             ->withNotification(Notification::create($title, $body));
    
//         try {
//             $this->messaging->send($message);
//         } catch (\Throwable $e) {
//             Log::error('Failed to send welcome notification: ' . $e->getMessage());
//         }
//     }
    

// }

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

        // Remove spaces for validation
        $data['card_number'] = str_replace(' ', '', $data['card_number']);

        $validatedData = Validator::make($data, [
            'name' => 'required|string|max:255|unique:users',
            'account_number' => 'required|string|size:21|unique:users',
            'password' => 'required|string|min:8',
            'card_number' => 'required|string|size:24|unique:users',
            'card_passcode' => 'required|digits:4',
            'device_token' => 'sometimes|nullable|string'
        ], [
            'card_number.size' => 'The card number must contain 24 characters excluding spaces.'
        ]);

        if ($validatedData->fails()) {
            return response()->json(['message' => $validatedData->errors()->first()], 400);
        }

        // Reformat card number for storage
        $formattedCardNumber = implode(' ', str_split($data['card_number'], 4));

        $user = new User([
            'name' => $data['name'],
            'account_number' => $data['account_number'],
            'password' => Hash::make($data['password']),
            'card_number' => $formattedCardNumber,
            'card_passcode' => $data['card_passcode'],
            'device_token' => $data['device_token'],
            'role' => 'user'
        ]);

        if ($user->save()) {
            $this->sendWelcomeNotification($user->device_token);
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'status' => 200,
                'success' => true,
                'token' => $token,
                'account_number' => $user->account_number,
                'message' => 'تم التسجيل بنجاح!'
            ]);
        } else {
            return response()->json(['message' => 'حدث خطأ ما، لم يتم حفظ المستخدم.'], 400);
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
