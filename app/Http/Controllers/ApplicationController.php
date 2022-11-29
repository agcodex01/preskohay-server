<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ApplicationRequest;
use App\Http\Implementations\SmsImplement;
use Illuminate\Support\Facades\Hash;

class ApplicationController extends Controller
{
    public function __construct(private SmsImplement $smsService)
    {

    }
    public function index()
    {
        return User::selectRaw('status, count(status) as total')
            ->where('user_role', 'driver')
            ->whereHas('application')
            ->groupBy('status')
            ->get();
    }

    public function list()
    {
        return User::where('user_role', 'driver')
            ->whereHas('application')
            ->with('application')
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    public function confirm(User $user)
    {
        try {
            // Note: user number must start to 63
            $end_number = substr($user->contact_number, 1, 11);
            $number = '63'.$end_number;

            $message = $this->smsService
                ->to($number)
                ->message('Your application is already confirmed.')
                ->send();

            if ($message->getStatus() == 0) {
                $user->update([
                    'status' => 'in_progress'
                ]);
                return 'ALready confirmed';
            } else {
                return $message->getStatus();
            }
        } catch (\Exception $th) {
            return $th;
        }
    }

    public function done(User $user)
    {
        return $user->update([
            'status' => 'done'
        ]);
    }

    public function decline(User $user)
    {
        return $user->update([
            'status' => 'declined'
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
