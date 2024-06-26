<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Kreait\Firebase\Factory;

class UserInformationController extends Controller
{
    private $messaging;

    public function __construct(Factory $firebase)
    {
        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
    }
    public function getUserInfo(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'المستخدم غير موجود'], 404);  
            }
            $firstCharacter = mb_substr($user->name, 0, 1); 
            return response()->json([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'first_character' => $firstCharacter, 
                'account_number' => $user->account_number,
                'card_number' => $user->card_number,
                'total_money' => $user->total_money,

            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'انتهت صلاحية الرمز'], 401);  
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'الرمز غير صالح'], 401); 
        } catch (\Exception $e) {
            return response()->json(['message' => 'الرمز التفويضي غير موجود'], 401);  // Authorization token not found in Arabic
        }
    }
}
