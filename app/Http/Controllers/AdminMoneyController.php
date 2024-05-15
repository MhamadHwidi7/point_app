<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;

class AdminMoneyController extends Controller
{   
    private $messaging;

  
    public function __construct(Factory $firebase)
    {
        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
    }
    
    public function fetchUser(Request $request)
    {
        $user = User::where('account_number', $request->input('account_number'))->first();
        if (!$user) {
            return back()->withErrors(['msg' => 'User not found']);
        }
        return back()->with('success', 'User found: ' . $user->account_number)->with('account_number', $user->account_number);
    }

    public function transferMoney(Request $request)
    {
        $user = User::where('account_number', $request->input('account_number'))->first();
    
        if (!$user) {
            Log::error('User not found with account number: ' . $request->input('account_number'));
            return back()->withErrors(['msg' => 'User not found']);
        }
    
        $pointsToAdd = (int) $request->input('amount');
        $user->total_money += $pointsToAdd;
        $user->save();

        if ($user->device_token) {
            $this->sendNotification($user, $pointsToAdd);
        }

        Log::info('Updated user points', ['account_number' => $user->account_number, 'newmoney' => $user->total_money]);
        return back()->with('success', 'Money updated successfully. Total Money: ' . $user->total_money);
    }

    private function sendNotification(User $receiver, float $amount)
    {
        $title = 'تم استلام المال';
        $body = "لقد استلمت $amount ريال.";

        $message = CloudMessage::withTarget('token', $receiver->device_token)
            ->withNotification(Notification::create($title, $body));

        try {
            $this->messaging->send($message);
            Log::info('Notification sent to receiver', ['receiver_id' => $receiver->id, 'amount' => $amount]);
        } catch (Exception $e) {
            Log::error('Failed to send FCM message: ' . $e->getMessage(), ['receiver_id' => $receiver->id]);
        }
    }
    
}
