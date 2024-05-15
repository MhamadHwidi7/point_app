<?php

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Auth;

// class RegisterController extends Controller
// {
//     public function showRegistrationForm()
//     {
//         return view('register');
//     }

   
//     public function register(Request $request)
//         {
//             $request->validate([
//                 'name' => 'required|unique:users,name',
//                 'account_number' => 'required|unique:users,account_number',
//                 'card_number' => 'required|unique:users,card_number',
//                 'card_passcode' => 'required',
//                 'password' => 'required|min:6',
                
//             ], [
//                 'name.unique' => 'الاسم موجود مسبقا، حاول مرة ثانية',
//                 'account_number.unique' => 'رقم الحساب موجود مسبقا، حاول مرة ثانية',
//                 'card_number.unique' => 'رقم البطاقة موجود مسبقا، حاول مرة ثانية',
//             ]);
        
//             // Proceed with the registration process if validation passes
        
        

//         $user = User::create([
//             'name' => $request->name,
//             'password' => Hash::make($request->password),
//             'role' => 'admin',  // Set default role as admin
//             'account_number' => $request->account_number,
//             'card_number' => $request->card_number,
//             'card_passcode' => $request->card_passcode,
//         ]);

//         $result = $user->save();
//         if($result){
//             $request->session()->put('loginId', $user->id);
//             return redirect('dashboard')->with('success','You have registered successfully.');
//         } else {
//             return back()->with('fail','Something wrong!');
//         }
//     }
//     }



namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('register');
    }

   
    public function register(Request $request)
        {
        // Remove spaces from card number to ensure it matches the 24 character requirement
        $cardNumberProcessed = str_replace(' ', '', $request->input('card_number'));
        
        // Manually merge processed card number back into the request data
        $request->merge(['card_number' => $cardNumberProcessed]);

        // Validate the input data
        $request->validate([
            'name' => 'required|unique:users,name',
            'account_number' => 'required|string|size:21|unique:users,account_number',
            'card_number' => 'required|string|size:24|unique:users,card_number',
            'card_passcode' => 'required|digits:4,card_passcode',
            'password' => 'required|min:6',
        ], [
            'name.unique' => 'الاسم موجود مسبقا، حاول مرة ثانية',
            'account_number.unique' => 'رقم الحساب موجود مسبقا، حاول مرة ثانية',
            'card_number.unique' => 'رقم البطاقة موجود مسبقا، حاول مرة ثانية',
            'card_number.size' => 'رقم البطاقة يجب أن يحتوي على 24 رمز بدون فراغات',
            'card_passcode.size' => 'رمز البطاقة يجب ان يحتوي 4  أرقام',
            'account_number.size' =>'رقم الحساب يجب أن يحتوي 21 رمز بدون فراغات'
        ]);

        // Create and save the user with validated data
        $user = User::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => 'admin',  // Set default role as admin
            'account_number' => $request->account_number,
            'card_number' => $request->card_number,
            'card_passcode' => $request->card_passcode,
        ]);

        $result = $user->save();
        if($result){
            $request->session()->put('loginId', $user->id);
         ///Todo:i change this 
            return redirect('dashboard_money')->with('success','تم انشاء الحساب  بنجاح');
        } else {
            return back()->with('fail','حدث شيء خاطئ');
        }
    }
    }

