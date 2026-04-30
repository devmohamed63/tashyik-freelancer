<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class PromotionController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('manage promotions');

        return view('dashboard.promotions.index');
    }
}
