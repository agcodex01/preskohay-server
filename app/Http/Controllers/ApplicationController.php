<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ApplicationRequest;
use Illuminate\Support\Facades\Session;

class ApplicationController extends Controller
{
    public function store(UserRequest $request)
    {
        return User::create($request->validated());
    }

    public function storeApplicationLicense(ApplicationRequest $request, User $user)
    {
        return $user->application()->create($request->validated());
    }

    public function storeApplicationMotor(ApplicationRequest $request, User $user)
    {
        return $user->application()->update($request->validated());
    }
}
