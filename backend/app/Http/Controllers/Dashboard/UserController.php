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

        $typeFilter = request('typeFilter');

        $totalCount   = User::isServiceProvider()->count();
        $pendingCount = User::isServiceProvider()->where('status', User::PENDING_STATUS)->count();
        $activeCount  = User::isServiceProvider()->where('status', User::ACTIVE_STATUS)->count();
        $inactiveCount = User::isServiceProvider()->where('status', User::INACTIVE_STATUS)->count();

        // Type-specific stats
        $institutionCount = User::isServiceProvider()->where('entity_type', User::INSTITUTION_ENTITY_TYPE)->count();
        $companyCount = User::isServiceProvider()->where('entity_type', User::COMPANY_ENTITY_TYPE)->count();
        $totalMembers = User::whereNotNull('institution_id')->count();

        return view('dashboard.users.service_providers', compact(
            'totalCount', 'pendingCount', 'activeCount', 'inactiveCount',
            'institutionCount', 'companyCount', 'totalMembers', 'typeFilter'
        ));
    }

    /**
     * Full page create service provider form.
     */
    public function create_service_provider()
    {
        Gate::authorize('create', User::class);

        return view('dashboard.users.create_service_provider');
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

        if ($request->filled('balance')) {
            $data['balance'] = $request->balance;
        }

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

    public function show_institution(User $user)
    {
        Gate::authorize('viewAny', User::class);

        abort_unless(in_array($user->entity_type, [
            User::INSTITUTION_ENTITY_TYPE,
            User::COMPANY_ENTITY_TYPE,
        ]), 404);

        $avatar = $user->getAvatarUrl('lg');

        $members = User::where('institution_id', $user->id)
            ->select(['id', 'name', 'phone', 'status', 'institution_id', 'created_at'])
            ->withCount([
                'serviceProviderOrders as completed_orders' => fn($q) => $q->completed(),
            ])
            ->withSum([
                'serviceProviderOrders as total_earnings' => fn($q) => $q->completed(),
            ], 'subtotal')
            ->orderBy('name')
            ->get();

        $activeMembers = $members->where('status', User::ACTIVE_STATUS)->count();
        $totalOrders = (int) $members->sum('completed_orders');
        $totalEarnings = number_format($members->sum('total_earnings') ?? 0, config('app.decimal_places'));

        return view('dashboard.users.show_institution', compact(
            'user', 'avatar', 'members', 'activeMembers', 'totalOrders', 'totalEarnings'
        ));
    }

    public function export_members(User $user)
    {
        Gate::authorize('viewAny', User::class);

        $columns = new \Illuminate\Support\Collection([
            \App\Utils\ExcelSheet\Column::name('name', __('validation.attributes.name')),
            \App\Utils\ExcelSheet\Column::name('phone', __('validation.attributes.phone')),
            \App\Utils\ExcelSheet\Column::name('email', __('validation.attributes.email'))
                ->callback(fn($m) => $m->email ?? '-'),
            \App\Utils\ExcelSheet\Column::name('status', __('ui.status'))
                ->callback(fn($m) => __('ui.' . $m->status)),
            \App\Utils\ExcelSheet\Column::name('city', __('ui.city'))
                ->relation('city', 'name'),
            \App\Utils\ExcelSheet\Column::name('completed_orders', __('ui.completed_orders'))
                ->customValue(fn($m) => $m->completed_orders ?? 0),
            \App\Utils\ExcelSheet\Column::name('total_earnings', __('ui.revenue') . ' (' . __('ui.currency') . ')')
                ->customValue(fn($m) => number_format($m->total_earnings ?? 0, config('app.decimal_places'))),
            \App\Utils\ExcelSheet\Column::name('created_at', __('ui.created_at'))
                ->dateFormat(),
        ]);

        $builder = User::where('institution_id', $user->id)
            ->select(['id', 'name', 'phone', 'email', 'status', 'city_id', 'created_at'])
            ->with('city:id,name')
            ->withCount([
                'serviceProviderOrders as completed_orders' => fn($q) => $q->completed(),
            ])
            ->withSum([
                'serviceProviderOrders as total_earnings' => fn($q) => $q->completed(),
            ], 'subtotal')
            ->orderBy('name');

        $excelSheet = new \App\Utils\ExcelSheet\ExcelSheet($columns, $builder);

        return $excelSheet->export("members-{$user->id}");
    }
}
