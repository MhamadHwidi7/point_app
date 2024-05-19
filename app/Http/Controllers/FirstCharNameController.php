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
                return response()->json(['message' => 'المستخدم غير موجود'], 404); 
            }
            return response()->json([
                'first_character' => substr($user->name, 0, 1)  
            ], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'غير مصرح'], 401);  
        } catch (\Exception $e) {
            return response()->json(['message' => 'خطأ في استرجاع الاسم'], 400); 
        }
    }
}