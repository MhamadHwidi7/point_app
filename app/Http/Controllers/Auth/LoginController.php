<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
{
    $request->validate([            
        'name'=>'required',
        'password'=>'required|min:8|max:12'
    ]);

    $user = User::where([
        ['name', '=', $request->name],
        ['role', '=', 'admin']
    ])->first();
    if($user){
        if(Hash::check($request->password, $user->password)){
            $request->session()->put('loginId', $user->id);
            return redirect('dashboard');
        } else {
            return back()->with('fail','Password not match!');
        }
    } else {
        return back()->with('fail','you must be admin to login.');
    }        
}
public function dashboard()
    {
        // return "Welcome to your dashabord.";
        $data = array();
        if(Session::has('loginId')){
            $data = User::where('id','=',Session::get('loginId'))->first();
        }
        return view('dashboard',compact('data'));
    }
    ///Logout
    
}
