<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AdminPointsController extends Controller
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

    public function updateUserPoints(Request $request)
    {
        $user = User::where('account_number', $request->input('account_number'))->first();
    
        if (!$user) {
            Log::error('User not found with account number: ' . $request->input('account_number'));
            return back()->withErrors(['msg' => 'User not found']);
        }
    
        $pointsToAdd = (int) $request->input('points');
        $user->total_points += $pointsToAdd;
        $user->save();

        if ($user->device_token) {
            $this->sendNotification($user, $pointsToAdd);
        }

        Log::info('Updated user points', ['account_number' => $user->account_number, 'newPoints' => $user->total_points]);
        return back()->with('success', 'Points updated successfully. Total points: ' . $user->total_points);
    }

    private function sendNotification(User $user, int $pointsToAdd)
    {
        $title = 'تحديث النقاط';
        $body = "لقد تلقيت $pointsToAdd نقاط. النقاط الإجمالية: {$user->total_points}";
    
        $message = CloudMessage::withTarget('token', $user->device_token)
            ->withNotification(Notification::create($title, $body));
    
        try {
            $this->messaging->send($message);
            Log::info('Notification sent', ['userId' => $user->id, 'pointsAdded' => $pointsToAdd]);
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            Log::error('Failed to send FCM message: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Unexpected error when sending FCM message: ' . $e->getMessage());
        }
    }
    
}

