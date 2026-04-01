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

        return view('dashboard.users.service_providers');
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

        return view('dashboard.users.edit', compact('user', 'avatar', 'roles', 'userRoles'));
    }

    /**
     * Update user.
     */
    public function update(User $user, UserRequest $request)
    {
        Gate::authorize('update', $user);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        if (!$user->hasRole('Super admin')) $user->syncRoles($request->roles);

        if ($request->image) {
            $user->addMediaFromRequest('image')->toMediaCollection('avatar');
        }

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }

    public function payout_requests()
    {
        Gate::authorize('viewAny', User::class);

        return view('dashboard.users.payout_requests');
    }
}
