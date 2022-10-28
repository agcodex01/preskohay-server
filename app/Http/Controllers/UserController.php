<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return $users;
    }

    public function store(UserRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['birthdate'] = Carbon::parse($data['birthdate']);

        return User::create($data);
    }

    public function update(UserRequest $request, User $user)
    {
        return $user->update($request->all());
    }

    public function destroy(User $user)
    {
        return $user->delete();
    }
}
