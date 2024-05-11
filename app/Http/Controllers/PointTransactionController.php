<?php

// namespace App\Http\Controllers;

// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Kreait\Firebase\Factory;
// use Kreait\Firebase\Messaging\CloudMessage;
// use Kreait\Firebase\Messaging\Notification;
// use Exception;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Http\Response;

// class PointTransactionController extends Controller
// {
//     private $messaging;

//     public function __construct(Factory $firebase)
//     {
//         $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
//         $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
//     }
//     public function getUserPoints(Request $request)
// {
//     // This retrieves the authenticated user based on the bearer token
//     $user = auth()->user();

//     // Check if there's an authenticated user
//     if (!$user) {
//         return response()->json(['message' => 'المصادقة مطلوبة'], 401);
//     }

//     try {
//         // Directly return the user's total points
//         return response()->json(['total_points' => $user->total_points]);
//     } catch (Exception $e) {
//         // Translate the not found message into Arabic
//         return response()->json(['message' => 'لم يتم العثور على البيانات'], Response::HTTP_NOT_FOUND);
//     }
// }
//     public function transferPoints(Request $request)
// {
//     // Check for user authentication
//     if (!auth()->check()) {
//         return response()->json(['message' => 'المصادقة مطلوبة'], 401);
//     }

//     // Validate the request parameters
//     $validated = $request->validate([
//         'sender_account_number' => 'required|string|exists:users,account_number',
//         'receiver_account_number' => 'required|string|exists:users,account_number',
//         'points' => 'required|numeric|min:1'
//     ]);

//     try {
//         // Transaction to transfer points
//         DB::transaction(function () use ($validated) {
//             $sender = User::where('account_number', $validated['sender_account_number'])->firstOrFail();
//             $receiver = User::where('account_number', $validated['receiver_account_number'])->firstOrFail();
//             $points = $validated['points'];

//             if ($sender->id === $receiver->id) {
//                 throw new Exception("لا يمكن تحويل النقاط إلى نفس الحساب");
//             }
//             if ($sender->total_points < $points) {
//                 throw new Exception("النقاط غير كافية");
//             }

//             $sender->total_points -= $points;
//             $sender->save();
//             $receiver->total_points += $points;
//             $receiver->save();

//             // Notify the receiver
//             $this->sendNotification($receiver, $points, $sender->name);
//         });
//         return response()->json(['message' => 'تم تحويل النقاط بنجاح']);
//     } catch (Exception $e) {
//         Log::error('Failed transaction attempt: ' . $e->getMessage());
//         return response()->json(['message' => $e->getMessage()], 400);
//     }
// }

//     private function sendNotification(User $receiver, int $points, string $senderName)
//     {
//         $title = 'تم استلام النقاط'; 
//         $body = "لقد استلمت $points نقطة من $senderName."; 
    
//         $message = CloudMessage::withTarget('token', $receiver->device_token)
//             ->withNotification(Notification::create($title, $body));

//         try {
//             $this->messaging->send($message);
//             Log::info('Notification sent to receiver', ['receiver_id' => $receiver->id, 'points' => $points]);
//         } catch (Exception $e) {
//             Log::error('Failed to send FCM message: ' . $e->getMessage(), ['receiver_id' => $receiver->id]);
//         }
//     }
// }

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
class PointTransactionController extends Controller
{
    private $messaging;

    public function __construct(Factory $firebase)
    {
        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
    }

    public function getUserPoints(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'المصادقة مطلوبة'], 401);
        }

        try {
            return response()->json(['total_points' => $user->total_points]);
        } catch (Exception $e) {
            return response()->json(['message' => 'لم يتم العثور على البيانات'], Response::HTTP_NOT_FOUND);
        }
    }

    public function transferPoints(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'المصادقة مطلوبة'], 401);
        }

        $validated = $request->validate([
            'sender_account_number' => 'required|string|size:21|exists:users,account_number',
            'receiver_account_number' => 'required|string|size:21|exists:users,account_number',
            'points' => 'required|numeric|min:1'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $sender = User::where('account_number', $validated['sender_account_number'])->firstOrFail();
                $receiver = User::where('account_number', $validated['receiver_account_number'])->firstOrFail();

                if ($sender->id === $receiver->id) {
                    throw new Exception("لا يمكن تحويل النقاط إلى نفس الحساب");
                }
                if ($sender->total_points < $validated['points']) {
                    throw new Exception("النقاط غير كافية");
                }

                $sender->total_points -= $validated['points'];
                $sender->save();
                $receiver->total_points += $validated['points'];
                $receiver->save();

                $this->sendNotification($receiver, $validated['points'], $sender->name);
            });
            return response()->json(['message' => 'تم تحويل النقاط بنجاح']);
        } catch (Exception $e) {
            Log::error('Failed transaction attempt: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    private function sendNotification(User $receiver, int $points, string $senderName)
    {
        $title = 'تم استلام النقاط';
        $body = "لقد استلمت $points نقطة من $senderName.";

        $message = CloudMessage::withTarget('token', $receiver->device_token)
            ->withNotification(Notification::create($title, $body));

        try {
            $this->messaging->send($message);
            Log::info('Notification sent to receiver', ['receiver_id' => $receiver->id, 'points' => $points]);
        } catch (Exception $e) {
            Log::error('Failed to send FCM message: ' . $e->getMessage(), ['receiver_id' => $receiver->id]);
        }
    }
}
