<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ApplicationRequest;

class ApplicationController extends Controller
{
    protected $user;

    public function store(UserRequest $request) 
    {
        $this->user = User::create($request->validated());

        return $this->user;
    }

    public function storeApplicationLicense(ApplicationRequest $request)
    {
        return $this->user->application()->create($request->validated());
    }

    public function storeApplicationMotor(ApplicationRequest $request)
    {
        return $this->user->application()->update($request->validated());
    }
}
