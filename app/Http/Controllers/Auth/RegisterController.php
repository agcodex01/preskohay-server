<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function execute(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);


        $user = User::create($data);

        return response([
            'user' => $user,
            'token' => $user->createToken('mobile_app')->plainTextToken
        ]);
    }
}
