<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {

            return [
                'user', $user,
                'token' => $user->createToken('mobile_app')->plainTextToken
            ];
        }

        return response([
            'errors' => [
                'email' => ['Invalid credentials.']
            ]
        ], 422);
    }

    public function logout(Request $request)
    {
        return $request->user()->currentAccessToken()->delete();
    }
}
