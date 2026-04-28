<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Support\Facades\Gate;

class PushAdsController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Banner::class);

        return view('dashboard.push-ads.index');
    }

    public function create()
    {
        Gate::authorize('create', Banner::class);

        return view('dashboard.push-ads.create');
    }
}
