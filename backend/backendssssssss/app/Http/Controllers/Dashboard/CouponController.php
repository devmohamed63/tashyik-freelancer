<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class CouponController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('manage coupons');

        return view('dashboard.coupons.index');
    }
}
