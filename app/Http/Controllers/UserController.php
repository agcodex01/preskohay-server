<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return $users;
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['birthdate'] = Carbon::parse($data['birthdate']);

        // check has file
        if ($request->hasFile('profile_image')) {
            $image_data = file_get_contents($data['profile_image']);
            $image_ext = $data['profile_image']->extension();
            $image_base64 = 'data:image/' . $image_ext . ';base64,' . base64_encode($image_data);
            $data['profile_image'] = $image_base64;
        }

        return User::create($data);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        return $user->update($request->validated());
    }

    public function updatePassword(UserRequest $request)
    {
        $user = Auth::user();

        $params = $request->validated();

        if (Hash::check($params['current'], $user->password)) {

            return $user->update([
                'password' => Hash::make($params['password'])
            ]);
        }

        return response([
            'errors' => [
                'current' => ['Current password is incorrect']
            ]
        ], 422);

    }

    public function destroy(User $user)
    {
        return $user->delete();
    }

    public function getUserById(User $user)
    {
        return $user;
    }
}
