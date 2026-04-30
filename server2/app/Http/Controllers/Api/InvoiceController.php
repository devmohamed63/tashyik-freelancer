<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends ApiController
{
    public function index()
    {
        Gate::authorize('viewAny', Invoice::class);

        /**
         * @var User
         */
        $serviceProvider = Auth::user();

        $invoices = Invoice::where('service_provider_id', $serviceProvider->id)
            ->orWhereRelation('serviceProvider', 'institution_id', $serviceProvider->id)
            ->orderByDesc('id')
            ->with('serviceProvider:id,name')
            ->paginate($this->paginationLimit, [
                'id',
                'service_provider_id',
                'target_id',
                'type',
                'action',
                'amount',
                'created_at'
            ]);

        return InvoiceResource::collection($invoices);
    }
}
