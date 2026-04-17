<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AddressController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $address = Auth::user()->addresses()->orderByDesc('id')->get();

        return AddressResource::collection($address);
    }

    /**
     * Store a new address
     */
    public function store(AddressRequest $request)
    {
        $user = $request->user();

        // Toggle default address
        if ($request->is_default) $user->defaultAddress()?->update(['is_default' => false]);

        $address = $user->addresses()->create($request->validated());

        return new AddressResource($address);
    }

    public function show(Address $address)
    {
        Gate::authorize('view', $address);

        return new AddressResource($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Address $address, AddressRequest $request)
    {
        Gate::authorize('update', $address);

        $user = $request->user();

        // Toggle default address
        if ($request->is_default) $user->defaultAddress()?->update(['is_default' => false]);

        $address->update($request->validated());

        return new AddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        Gate::authorize('delete', $address);

        $address->delete();

        return response('');
    }
}
