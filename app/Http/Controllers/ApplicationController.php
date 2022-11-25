<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ApplicationRequest;
use App\Http\Services\SmsService;
use Illuminate\Support\Facades\Hash;

class ApplicationController extends Controller
{
    public function __construct(private SmsService $smsService)
    {

    }
    public function index()
    {
        return User::selectRaw('status, count(status) as total')
            ->where('user_role', 'driver')
            ->groupBy('status')
            ->get();
    }

    public function list()
    {
        return User::where('user_role', 'driver')
            ->with('application')
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    public function confirm(User $user)
    {
        $this->smsService
            ->to($user->contact_number)
            ->message('Your application already confirmed!')
            ->send();

        return $user->update([
            'status' => 'on_progress'
        ]);
    }

    public function done(User $user)
    {
        return $user->update([
            'status' => 'done'
        ]);
    }

    public function store(UserRequest $request)
    {
        $params = $request->validated();
        $params['password'] = Hash::make($params['password']);
        return User::create($params);
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
