<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InstitutionMemberController extends Controller
{
    public function index()
    {
        /** @var User */
        $user = Auth::user();

        // Only institution/company owners can access
        abort_unless(
            $user->isInstitutionOrCompany() && $user->status === User::ACTIVE_STATUS,
            403
        );

        $members = User::where('institution_id', $user->id)
            ->select(['id', 'name', 'phone', 'status', 'created_at'])
            ->withCount([
                'serviceProviderOrders as total_orders',
                'serviceProviderOrders as completed_orders' => fn($q) => $q->completed(),
                'serviceProviderOrders as this_month_orders' => fn($q) =>
                    $q->completed()->whereMonth('updated_at', now()->month)
                        ->whereYear('updated_at', now()->year),
            ])
            ->withSum([
                'serviceProviderOrders as total_earnings' => fn($q) => $q->completed(),
            ], 'subtotal')
            ->withSum([
                'serviceProviderOrders as this_month_earnings' => fn($q) =>
                    $q->completed()->whereMonth('updated_at', now()->month)
                        ->whereYear('updated_at', now()->year),
            ], 'subtotal')
            ->orderBy('name')
            ->limit(100)
            ->get();

        // Add avatar URL and format dates
        $membersData = $members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'picture' => $member->getAvatarUrl('lg'),
                'status' => $member->status,
                'total_orders' => (int) $member->total_orders,
                'completed_orders' => (int) $member->completed_orders,
                'this_month_orders' => (int) $member->this_month_orders,
                'total_earnings' => number_format($member->total_earnings ?? 0, config('app.decimal_places')),
                'this_month_earnings' => number_format($member->this_month_earnings ?? 0, config('app.decimal_places')),
                'joined_at' => $member->created_at->isoFormat(config('app.time_format')),
            ];
        });

        // Summary for the institution
        $summary = [
            'total_members' => $members->count(),
            'active_members' => $members->where('status', User::ACTIVE_STATUS)->count(),
            'total_orders' => (int) $members->sum('completed_orders'),
            'total_earnings' => number_format($members->sum('total_earnings') ?? 0, config('app.decimal_places')),
            'this_month_orders' => (int) $members->sum('this_month_orders'),
            'this_month_earnings' => number_format($members->sum('this_month_earnings') ?? 0, config('app.decimal_places')),
            'balance' => number_format($user->balance, config('app.decimal_places')),
            'currency' => __('ui.currency'),
        ];

        return response()->json([
            'data' => $membersData,
            'summary' => $summary,
        ]);
    }
}
