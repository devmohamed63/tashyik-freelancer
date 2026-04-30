<x-layouts.dashboard page="show_institution">

    <x-dashboard.breadcrumb :page="__('ui.show_institution') . ' - ' . $user->name">
        @can('viewAny', App\Models\User::class)
            <x-dashboard.breadcrumb-back
                :url="route('dashboard.users.service_providers', ['typeFilter' => 'institution'])"
                :name="__('ui.back_to_service_providers')" />
        @endcan
    </x-dashboard.breadcrumb>

    {{-- ═══════════════════════════════════════════════════════
         Section 1: Institution Header
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $avatar }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover ring-2 ring-gray-100 dark:ring-gray-700" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
                <div class="w-16 h-16 rounded-2xl bg-brand-100 dark:bg-brand-900/40 ring-2 ring-gray-100 dark:ring-gray-700 items-center justify-center text-brand-600 dark:text-brand-400 font-bold text-xl" style="display:none">{{ mb_substr($user->name, 0, 2) }}</div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        {{ $user->name }}
                        @if ($user->entity_type === 'institution')
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400">{{ __('ui.institution') }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-400">{{ __('ui.company') }}</span>
                        @endif
                        @switch($user->status)
                            @case('active')
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">{{ __('ui.active') }}</span>
                            @break
                            @case('pending')
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">{{ __('ui.pending') }}</span>
                            @break
                            @case('inactive')
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">{{ __('ui.inactive') }}</span>
                            @break
                        @endswitch
                    </h2>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                        <span dir="ltr">{{ $user->phone }}</span>
                        @if ($user->email)
                            <span>{{ $user->email }}</span>
                        @endif
                        <span>{{ __('ui.created_at') }}: <strong class="text-gray-700 dark:text-gray-300">{{ $user->created_at->isoFormat(config('app.time_format')) }}</strong></span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="showModal('addMemberModal')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                    {{-- material-symbols:person-add --}}
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 14v-3h-3V9h3V6h2v3h3v2h-3v3zm-9-2q-1.65 0-2.825-1.175T5 8t1.175-2.825T9 4t2.825 1.175T13 8t-1.175 2.825T9 12m-8 8v-2.8q0-.85.438-1.562T2.6 14.55q1.975-.975 4.038-1.462T9 12.6t2.363.488t4.037 1.462q.725.35 1.163 1.063T17 17.2V20z"/></svg>
                    {{ __('ui.add_member') }}
                </button>
                <a href="{{ route('dashboard.users.edit', $user) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-700 rounded-xl hover:bg-brand-100 dark:hover:bg-brand-900/50 transition-colors">
                    {{-- material-symbols:edit --}}
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M5 19h1.4l8.625-8.625l-1.4-1.4L5 17.6zM19.3 8.925l-4.25-4.2l1.4-1.4q.575-.575 1.413-.575t1.412.575l1.4 1.4q.575.575.6 1.388t-.55 1.387zM4 21q-.425 0-.712-.288T3 20v-2.825q0-.2.075-.387t.225-.338l10.3-10.3l4.25 4.25l-10.3 10.3q-.15.15-.337.225T6.825 21z"/></svg>
                    {{ __('ui.edit') }}
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 2: Summary Stats Cards
    ═══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {{-- Active Members --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M16.675 18.7a2.65 2.65 0 0 1-1.26 2.48c-.418.257-.9.392-1.39.39H4.652a2.63 2.63 0 0 1-1.39-.39A2.62 2.62 0 0 1 2.01 18.7a2.6 2.6 0 0 1 .5-1.35a8.8 8.8 0 0 1 6.812-3.51a8.78 8.78 0 0 1 6.842 3.5a2.7 2.7 0 0 1 .51 1.36M14.245 7.32a4.92 4.92 0 0 1-4.902 4.91a4.903 4.903 0 0 1-4.797-5.858a4.9 4.9 0 0 1 6.678-3.57a4.9 4.9 0 0 1 3.03 4.518z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeMembers }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.active_members') }}</p>
        </div>

        {{-- Total Orders --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalOrders) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.total_institution_orders') }}</p>
        </div>

        {{-- Total Earnings --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalEarnings }} {{ __('ui.currency') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.total_institution_earnings') }}</p>
        </div>

        {{-- Balance --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 text-center">
            <div class="mx-auto w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M21 18v1c0 1.1-.9 2-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14c1.1 0 2 .9 2 2v1h-9a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2zm-9-2h10V8H12zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5s1.5.67 1.5 1.5s-.67 1.5-1.5 1.5" /></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($user->balance, config('app.decimal_places')) }} {{ __('ui.currency') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.balance') }}</p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         Section 3: Members Table
    ═══════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 md:p-6">

        {{-- Members Header --}}
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M16.675 18.7a2.65 2.65 0 0 1-1.26 2.48c-.418.257-.9.392-1.39.39H4.652a2.63 2.63 0 0 1-1.39-.39A2.62 2.62 0 0 1 2.01 18.7a2.6 2.6 0 0 1 .5-1.35a8.8 8.8 0 0 1 6.812-3.51a8.78 8.78 0 0 1 6.842 3.5a2.7 2.7 0 0 1 .51 1.36M14.245 7.32a4.92 4.92 0 0 1-4.902 4.91a4.903 4.903 0 0 1-4.797-5.858a4.9 4.9 0 0 1 6.678-3.57a4.9 4.9 0 0 1 3.03 4.518z" /></svg>
                {{ __('ui.members', ['count' => $members->count()]) }}
            </h3>
            @if ($members->count())
                <a href="{{ route('dashboard.institution.export_members', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm4 18H6V4h7v5h5zm-5.1-5.4L11 12.5l1.9-2.1H15l-2.8 3l2.8 3h-2.1l-1.9-2.1L9.1 16.4H7l2.8-3l-2.8-3h2.1z" /></svg>
                    {{ __('ui.export') }}
                </a>
            @endif
        </div>

        @if ($members->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">{{ __('validation.attributes.name') }}</th>
                            <th class="px-4 py-3">{{ __('validation.attributes.phone') }}</th>
                            <th class="px-4 py-3">{{ __('ui.status') }}</th>
                            <th class="px-4 py-3">{{ __('ui.completed_orders') }}</th>
                            <th class="px-4 py-3">{{ __('ui.revenue') }} ({{ __('ui.currency') }})</th>
                            <th class="px-4 py-3">{{ __('ui.created_at') }}</th>
                            <th class="px-4 py-3">{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($members as $index => $member)
                            <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-3 text-gray-400 font-mono">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3 justify-center">
                                        <img src="{{ $member->getAvatarUrl('sm') }}" alt="{{ $member->name }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700" />
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono" dir="ltr">{{ $member->phone }}</td>
                                <td class="px-4 py-3">
                                    @if($member->status === 'active')
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">{{ __('ui.active') }}</span>
                                    @elseif($member->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">{{ __('ui.pending') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">{{ __('ui.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-bold text-blue-600 dark:text-blue-400">{{ number_format($member->completed_orders) }}</td>
                                <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($member->total_earnings ?? 0, config('app.decimal_places')) }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $member->created_at->isoFormat(config('app.time_format')) }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('dashboard.users.edit', $member) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-700 rounded-lg hover:bg-brand-100 dark:hover:bg-brand-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5M12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5s5 2.24 5 5s-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3s-1.34-3-3-3" /></svg>
                                        {{ __('ui.view') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                <svg class="w-12 h-12 mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><line x1="17" y1="11" x2="22" y2="11" />
                </svg>
                <p class="text-sm font-medium">{{ __('ui.no_results') }}</p>
            </div>
        @endif
    </div>

    {{-- Add Member Modal --}}
    <x-dashboard.modals.default id="addMemberModal">
        @livewire('dashboard.users.create', ['prefilledInstitution' => (string) $user->id])
    </x-dashboard.modals.default>

</x-layouts.dashboard>
