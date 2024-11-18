<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        
        return response()->json([
            'user' => $user,
        ]);
    }
    public function login(Request $request){
        $user = User::where('email' , $request->email)->first();
        if(!$user || Hash::check($request->password, $user->password)){
            return response()->json([
                'message' => 'User not found or password is incorrect'
            ], 404);
        }
        $token = $user->createToken()->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ],200);
    }
}
