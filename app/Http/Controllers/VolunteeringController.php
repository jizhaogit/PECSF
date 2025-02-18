<?php

namespace App\Http\Controllers;

use App\Http\Requests\VolunteerRegistrationRequest;
use App\Models\Organization;
use App\Models\User;
use App\Models\Pledge;
use App\Models\Volunteer;
use Illuminate\Support\Facades\Auth;

class VolunteeringController extends Controller
{
    public function index() {
        $organizations = Organization::all();
        $user = User::find(Auth::id());
        $totalPledgedDataTillNow = Pledge::where('user_id', Auth::id())->sum('goal_amount');
        return view('volunteering.index', compact('organizations', 'user', 'totalPledgedDataTillNow'));
    }

    public function store(VolunteerRegistrationRequest $request) {
        $input = $request->validated();
        $input['user_id'] = Auth::id();
        Volunteer::create($input);
        return redirect()->route('volunteering.index');
    }
}
