<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class SubscriptionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        Gate::authorize('manage subscriptions');

        return view('dashboard.subscriptions.index');
    }
}
