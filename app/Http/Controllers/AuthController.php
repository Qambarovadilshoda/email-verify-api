<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->verification_token = uniqid();
        $user->save();

        SendEmailJob::dispatch($user);

        return response()->json([
            'user' => $user,
        ]);
    }
    public function login(LoginRequest $request){
        $user = User::where('email' , $request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([
                'message' => 'User not found or password is incorrect'
            ], 404);
        }
        if($user->email_verified_at == null){
            return response()->json([
                'message' => 'Email not verified'
            ], 403);
        }
        $token = $user->createToken('login')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ],200);
    }
    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'User logged out successfully'
        ]);
    }
    public function verifyEmail(Request $request){
        $user = User::where('verification_token' , $request->token)->first();
        if(!$user)
        {
            return response()->json([
                'message' => 'User not found'
            ],404);
        }
        $user->email_verified_at = now();
        $user->save();
        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }
}
