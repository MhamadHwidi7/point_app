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
        $validatedData = Validator::make($data, [
            'name' => 'required|string|max:255|unique:users',
            'account_number' => 'required|string|max:25|unique:users',
            'password' => 'required|string|min:8',
            'card_number' => 'required|string|max:25|unique:users', 
            'card_passcode' => 'required|digits:4',  
            'device_token' => 'sometimes|string' 
        ]);
    
        if ($validatedData->fails()) {
            return response()->json([
                'message' => $validatedData->errors()->first() // Returns the first validation error message in Arabic
            ], 400);
        }
    
        $user = new User;
        $user->name = $data['name'];
        $user->account_number = $data['account_number'];
        $user->password = Hash::make($data['password']);
        $user->card_number = $data['card_number']; 
        $user->card_passcode = $data['card_passcode']; 
        $user->device_token = $data['device_token'] ?? null;
        $user->role = 'user';
    
        if ($user->save()) {
            $this->sendWelcomeNotification($user->device_token);
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'status' => 200,
                'success' => true,
                'token' => $token,
                'account_number' => $user->account_number, // Include account number in the response
                'message' => 'تم التسجيل بنجاح!' // Your registration has been successfully
            ]);
        } else {
            return response()->json([
                'message' => 'حدث خطأ ما، لم يتم حفظ المستخدم.' // Something went wrong, unable to save the user.
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
