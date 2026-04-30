<?php

namespace App\Http\Controllers\Api;

use App\Events\NewPayoutRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\PayoutRequest;
use App\Models\User;

class RequestPayout extends Controller
{
    /**
     * Submit a new payout request
     */
    public function __invoke()
    {
        Gate::authorize('viewAny', PayoutRequest::class);

        /**
         * @var User
         */
        $service_provider = Auth::user();

        // Members cannot request payout — only institution owner can
        abort_if($service_provider->institution_id, 403);

        // Check if your service provider has no pending payment request
        if ($service_provider->balance > 0 && !$service_provider->payoutRequest) {
            $payoutRequest = $service_provider->payoutRequest()->create();

            NewPayoutRequest::dispatch($payoutRequest);
        }

        return response('');
    }
}
