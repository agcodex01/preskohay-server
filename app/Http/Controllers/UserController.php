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

        return [
            'data' => $users,
            'message' => 'Successfully retrieved!',
            'error' => false,
        ];
    }

    public function store(UserRequest $request)
    {
        $data = $request->all();

        $data['password'] = Hash::make($data['password']);
        $data['birthdate'] = Carbon::parse($data['birthdate']);

        DB::beginTransaction();
        try {
            $user = User::create($data);
            DB::commit();
            return [
                'message' => 'Successfully created!',
                'data' => $user,
                'error' => false
            ];

        } catch (\Exception $e) {
            return $e;
            DB::rollback();
            return [
                'message' => 'Something wen\'t wrong',
                'data' => null,
                'error' => true
            ];
        }
    }

    public function update(UserRequest $request, User $user)
    {
        DB::beginTransaction();
        try {
            $user->update($request->all());
            DB::commit();
            return [
                    'message' => 'Successfully updated!',
                    'data' => $user,
                    'error' => false
                ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'message' => 'Something wen\'t wrong',
                'data' => null,
                'error' => true
            ];
        }
    }

    public function destroy(User $user)
    {
        $user->delete();
        return [
                'message' => 'Successfully deleted!',
                'data' => $user,
                'error' => false
            ];
    }
}
