<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Support\Facades\Gate;

class CityController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', City::class);

        return view('dashboard.cities.index');
    }
}
