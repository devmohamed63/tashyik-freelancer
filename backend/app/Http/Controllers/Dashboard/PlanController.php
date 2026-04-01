<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class PlanController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        Gate::authorize('manage plans');

        return view('dashboard.plans.index');
    }
}
