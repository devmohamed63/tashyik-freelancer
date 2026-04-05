<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return view('dashboard.users.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function service_providers()
    {
        Gate::authorize('viewAny', User::class);

        $totalCount   = User::isServiceProvider()->count();
        $pendingCount = User::isServiceProvider()->where('status', User::PENDING_STATUS)->count();
        $activeCount  = User::isServiceProvider()->where('status', User::ACTIVE_STATUS)->count();
        $inactiveCount = User::isServiceProvider()->where('status', User::INACTIVE_STATUS)->count();

        return view('dashboard.users.service_providers', compact(
            'totalCount', 'pendingCount', 'activeCount', 'inactiveCount'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', User::class);

        $roles = Role::get(['id', 'name']);

        return view('dashboard.users.create', compact('roles'));
    }

    /**
     * Store user.
     */
    public function store(UserRequest $request)
    {
        Gate::authorize('create', User::class);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password)
        ]);

        $user->assignRole($request->roles);

        $user->addMediaFromRequest('image')->toMediaCollection('avatar');

        return redirect()->back()->with(['status' => __('ui.added_successfully')]);
    }

    /**
     * Edit user form.
     */
    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        $avatar = $user->getAvatarUrl('lg');

        $roles = Role::get(['id', 'name']);

        $userRoles = $user->roles->pluck(['name']);

        $isServiceProvider = $user->type !== User::USER_ACCOUNT_TYPE;

        $cities = \App\Models\City::orderBy('name')->get(['id', 'name']);

        $categories = \App\Models\Category::isParent()->orderBy('name')->get(['id', 'name']);

        $userCategories = $user->categories->pluck('id')->toArray();

        $residenceImage = $user->getMedia('residence_image')->first()?->getUrl('xl');
        $commercialRegistrationImage = $user->getMedia('commercial_registration_image')->first()?->getUrl('xl');

        return view('dashboard.users.edit', compact(
            'user', 'avatar', 'roles', 'userRoles',
            'isServiceProvider', 'cities', 'categories', 'userCategories',
            'residenceImage', 'commercialRegistrationImage'
        ));
    }

    /**
     * Update user.
     */
    public function update(User $user, UserRequest $request)
    {
        Gate::authorize('update', $user);

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
        ];

        // Service provider fields
        if ($user->type !== User::USER_ACCOUNT_TYPE) {
            if ($request->filled('status')) $data['status'] = $request->status;
            if ($request->filled('entity_type')) $data['entity_type'] = $request->entity_type;
            if ($request->filled('city_id')) $data['city_id'] = $request->city_id;
            if ($request->has('bank_name')) $data['bank_name'] = $request->bank_name;
            if ($request->has('iban')) $data['iban'] = $request->iban;
            if ($request->has('residence_name')) $data['residence_name'] = $request->residence_name;
            if ($request->has('residence_number')) $data['residence_number'] = $request->residence_number;
            if ($request->has('commercial_registration_number')) $data['commercial_registration_number'] = $request->commercial_registration_number;
            if ($request->has('tax_registration_number')) $data['tax_registration_number'] = $request->tax_registration_number;
            if ($request->filled('balance')) $data['balance'] = $request->balance;
        }

        $user->update($data);

        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        if (!$user->hasRole('Super admin')) $user->syncRoles($request->roles);

        // Categories (service providers)
        if ($user->type !== User::USER_ACCOUNT_TYPE && $request->has('categories')) {
            $user->categories()->sync($request->categories ?? []);
        }

        if ($request->image) {
            $user->addMediaFromRequest('image')->toMediaCollection('avatar');
        }

        if ($request->hasFile('residence_image')) {
            $user->addMediaFromRequest('residence_image')->toMediaCollection('residence_image');
        }

        if ($request->hasFile('commercial_registration_image')) {
            $user->addMediaFromRequest('commercial_registration_image')->toMediaCollection('commercial_registration_image');
        }

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }

    public function payout_requests()
    {
        Gate::authorize('viewAny', User::class);

        return view('dashboard.users.payout_requests');
    }
}
