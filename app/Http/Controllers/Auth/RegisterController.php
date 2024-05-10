<?php

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
            $request->validate([
                'name' => 'required|unique:users,name',
                'account_number' => 'required|unique:users,account_number',
                'card_number' => 'required|unique:users,card_number',
                'card_passcode' => 'required',
                'password' => 'required|min:6',
                
            ], [
                'name.unique' => 'الاسم موجود مسبقا، حاول مرة ثانية',
                'account_number.unique' => 'رقم الحساب موجود مسبقا، حاول مرة ثانية',
                'card_number.unique' => 'رقم البطاقة موجود مسبقا، حاول مرة ثانية',
            ]);
        
            // Proceed with the registration process if validation passes
        
        

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
            return redirect('dashboard')->with('success','You have registered successfully.');
        } else {
            return back()->with('fail','Something wrong!');
        }
    }
    }

