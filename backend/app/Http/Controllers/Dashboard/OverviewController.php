<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OverviewController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('view dashboard');

        $overviewData = Cache::remember('dashboard-overview', now()->endOfHour(), function () {
            $usersCount = number_format(User::isUser()->count());
            $serviceProvidersCount = number_format(User::notUser()->count());
            $activeSubscriptionsCount = number_format(Subscription::query()->active()->count());
            $inactiveSubscriptionsCount = number_format(Subscription::query()->inactive()->count());
            $payoutRequestsCount = number_format(PayoutRequest::count());
            $newOrdersCount = number_format(Order::isNew()->count());
            $onProgressOrdersCount = number_format(Order::started()->count());
            $completedOrdersCount = number_format(Order::completed()->count());
            $contactRequestCount = number_format(Contact::count());

            // Charts
            $serviceProviderCategories = Category::isParent()
                ->withCount('serviceProviders')
                ->get(['id', 'name']);

            $serviceProviderCities = User::notUser()
                ->whereNotNull('city_id')
                ->with('city:id,name')
                ->select(DB::raw('count(*) as count'), 'city_id')
                ->groupBy('city_id')
                ->get();

            return compact(
                'usersCount',
                'serviceProvidersCount',
                'activeSubscriptionsCount',
                'inactiveSubscriptionsCount',
                'payoutRequestsCount',
                'newOrdersCount',
                'onProgressOrdersCount',
                'completedOrdersCount',
                'contactRequestCount',

                // Charts
                'serviceProviderCategories',
                'serviceProviderCities',
            );
        });

        return view('dashboard.overview.index', $overviewData);
    }
}
