<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request){
        $validate = $request->validate([
            'email'=>'required|email',
            'password'=>'required|string|min:6'
        ]);
        Log::info('Attempting');
        if(!Auth::attempt(['email'=>$validate['email'],'password'=>$validate['password']])){
            return response()->json(['message'=>'Invalid Credentials']);
        }
        Log::info('Attempt Passed');
        $token = Auth::user()->createToken('API TOKEN')->plainTextToken;
        
        return response()->json(['message'=>'Successfully Authenticated','token'=>$token]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        return response()->json(['message' => 'User registered successfully']);
    }
    
    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message'=>'Logout Successfully']);
    }
}