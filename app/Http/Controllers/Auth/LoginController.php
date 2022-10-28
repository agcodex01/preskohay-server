<?php

namespace App\Http\Controllers;

use Hash;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('authentication_token');

                $data = [
                    'user' => $user,
                    'toke' => $token->plainTextToken
                ];
                return [
                    'error' => false,
                    'data'  => $data,
                    'message' => 'Login Succesfully'
                ];
            }
        }
        return [
            'error' => true,
            'message' => 'Incorrect email or password!',
            'data' => null
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'error' => false,
            'message' => 'Successfully logout!',
            'data' => null
        ]);
    }
}
