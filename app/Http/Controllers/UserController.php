<?php

namespace App\Http\Controllers;

use DB;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json([
            'data' => $user,
            'message' => 'Successfully retrieved!',
            'error' => false,
        ]);
    }

    public function store(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create($request->all());
            DB::commit();
            return response()->json(
                [
                    'message' => 'Successfully created!',
                    'data' => $user,
                    'error' => false
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Something wen\'t wrong',
                'data' => null,
                'error' => true
            ]); 
        }
    }

    public function update(UserRequest $request, User $user)
    {
        DB::beginTransaction();
        try {
            $user->update($request->all());
            DB::commit();
            return response()->json(
                [
                    'message' => 'Successfully updated!',
                    'data' => $user,
                    'error' => false
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Something wen\'t wrong',
                'data' => null,
                'error' => true
            ]);
        }
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(
            [
                'message' => 'Successfully deleted!',
                'data' => $user,
                'error' => false
            ]
        );
    }
}
