<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FinancialReportsController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view financial reports');

        $period = $request->get('period', 'all');
        $decimal = config('app.decimal_places');

        // ── Date constraints ────────────────────────────────
        $dateConstraint = match ($period) {
            'today' => fn($q) => $q->whereDate('created_at', today()),
            'week'  => fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()]),
            'month' => fn($q) => $q->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month),
            default => fn($q) => $q,
        };

        // ── Invoice-based financials ────────────────────────
        $invoiceQuery = fn(string $type, string $action = Invoice::CREDIT_ACTION) => Invoice::query()
            ->where('type', $type)
            ->where('action', $action)
            ->tap($dateConstraint);

        $orderRevenue        = number_format($invoiceQuery(Invoice::COMPLETED_ORDER_TYPE)->sum('amount'), $decimal);
        $additionalRevenue   = number_format($invoiceQuery(Invoice::ADDITIONAL_SERVICES_TYPE)->sum('amount'), $decimal);
        $orderTax            = number_format($invoiceQuery(Invoice::COMPLETED_ORDER_TAX_TYPE)->sum('amount'), $decimal);
        $additionalTax       = number_format($invoiceQuery(Invoice::ADDITIONAL_SERVICES_TAX_TYPE)->sum('amount'), $decimal);
        $subscriptionRevenue = number_format($invoiceQuery(Invoice::RENEW_SUBSCRIPTION_TYPE)->sum('amount'), $decimal);
        $subscriptionTax     = number_format($invoiceQuery(Invoice::RENEW_SUBSCRIPTION_TAX_TYPE)->sum('amount'), $decimal);
        $bankTransfers       = number_format(
            $invoiceQuery(Invoice::BANK_TRANSFER_TYPE, Invoice::DEBIT_ACTION)->sum('amount'),
            $decimal
        );

        // ── Totals ──────────────────────────────────────────
        $totalRevenue = number_format(
            Invoice::query()
                ->where('action', Invoice::CREDIT_ACTION)
                ->whereIn('type', [
                    Invoice::COMPLETED_ORDER_TYPE,
                    Invoice::ADDITIONAL_SERVICES_TYPE,
                    Invoice::RENEW_SUBSCRIPTION_TYPE,
                ])
                ->tap($dateConstraint)
                ->sum('amount'),
            $decimal
        );

        $totalTax = number_format(
            Invoice::query()
                ->whereIn('type', [
                    Invoice::COMPLETED_ORDER_TAX_TYPE,
                    Invoice::ADDITIONAL_SERVICES_TAX_TYPE,
                    Invoice::RENEW_SUBSCRIPTION_TAX_TYPE,
                ])
                ->tap($dateConstraint)
                ->sum('amount'),
            $decimal
        );

        $pendingPayouts = number_format(
            User::where('type', '!=', User::USER_ACCOUNT_TYPE)->sum('balance'),
            $decimal
        );

        $payoutRequestsCount = PayoutRequest::count();



        // ── Period labels ───────────────────────────────────
        $periodLabels = [
            'all'   => 'الكل',
            'today' => 'اليوم',
            'week'  => 'الأسبوع',
            'month' => 'الشهر',
        ];

        return view('dashboard.financial-reports.index', compact(
            'period',
            'periodLabels',
            'orderRevenue',
            'additionalRevenue',
            'orderTax',
            'additionalTax',
            'subscriptionRevenue',
            'subscriptionTax',
            'bankTransfers',
            'totalRevenue',
            'totalTax',
            'pendingPayouts',
            'payoutRequestsCount',
        ));
    }
}
