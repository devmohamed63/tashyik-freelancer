<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use App\Models\City;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class AnalyticsController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('view dashboard');

        $categories = Category::isParent()->get(['id', 'name']);

        $cities = City::get(['id', 'name']);

        return view('dashboard.analytics.index', compact('categories', 'cities'));
    }
}
