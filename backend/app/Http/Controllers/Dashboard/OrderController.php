<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        Gate::authorize('manage orders');

        return view('dashboard.orders.index');
    }
}
