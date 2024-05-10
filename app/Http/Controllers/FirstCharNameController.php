<?php

namespace App\Http\Controllers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;

class FirstCharNameController extends Controller
{
    public function getFirstCharacterOfName(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'المستخدم غير موجود'], 404);  // User not found in Arabic
            }
            return response()->json([
                'first_character' => substr($user->name, 0, 1)  // Get the first character of the user's name
            ], 200);
        } catch (JWTException $e) {
            // This will handle errors such as token expired or token invalid
            return response()->json(['message' => 'غير مصرح'], 401);  // Unauthorized in Arabic
        } catch (\Exception $e) {
            return response()->json(['message' => 'خطأ في استرجاع الاسم'], 400);  // Error retrieving name in Arabic
        }
    }
}