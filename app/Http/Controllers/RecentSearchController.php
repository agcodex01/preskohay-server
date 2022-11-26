<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RecentSearchRequest;

class RecentSearchController extends Controller
{
    public function store(RecentSearchRequest $request)
    {
        $user = Auth::user();
        return $user->searches()->create($request->validated());
    }
}
