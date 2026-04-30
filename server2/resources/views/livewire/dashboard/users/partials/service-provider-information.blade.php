<x-dashboard.ui.hr />

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

    <!-- Type -->
    <x-dashboard.info-label
        :name="__('ui.account_type')"
        :value="__('ui.' . $user?->entity_type)" />

    @if ($user->entity_type == \App\Models\User::INDIVIDUAL_ENTITY_TYPE)
        <!-- Institution -->
        <x-dashboard.info-label :name="__('ui.linked_institution')">
            @if ($user->institution_id)
                <button wire:click="$dispatch('show-result', { id: {{ $user->institution_id }} })" class="inline-flex gap-1 items-center cursor-pointer group">
                    <span class="transition-colors group-hover:text-brand-500 text-gray-600 dark:text-gray-400 text-nowrap">{{ $user->institution->name }}</span>
                    <!-- ic:round-remove-red-eye -->
                    <svg class="transition-colors group-hover:text-brand-500 text-gray-400 dark:text-gray-500 w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
                        <path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5M12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5s5 2.24 5 5s-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3s-1.34-3-3-3" />
                    </svg>
                </button>
            @else
                <span class="text-gray-600 dark:text-gray-400">{{ __('ui.none') }}</span>
            @endif
        </x-dashboard.info-label>
    @endif

    <!-- Status -->
    <div x-data="{ edit: false }" class="flex flex-col gap-2 w-fit">
        <label @click="edit = !edit" class="inline-flex cursor-pointer gap-2 items-center w-fit text-xs leading-normal text-gray-500 dark:text-gray-400">
            {{ __('validation.attributes.status') }}
            <!-- material-symbols:edit-square -->
            <svg xmlns="http://www.w3.org/2000/svg" class="text-gray-400 dark:text-gray-500 w-4 h-4" viewBox="0 0 24 24"><!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
                <path fill="currentColor" d="M9 15v-4.25l9.175-9.175q.3-.3.675-.45t.75-.15q.4 0 .763.15t.662.45L22.425 3q.275.3.425.663T23 4.4t-.137.738t-.438.662L13.25 15zm10.6-9.2l1.425-1.4l-1.4-1.4L18.2 4.4zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8.925L7 9.925V17h7.05L21 10.05V19q0 .825-.587 1.413T19 21z" />
            </svg>
        </label>
        <div x-show="!edit">
            @switch($status)
                @case(App\Models\User::PENDING_STATUS)
                    <x-dashboard.badges.warning :name="__('ui.' . $status)" />
                @break

                @case(App\Models\User::ACTIVE_STATUS)
                    <x-dashboard.badges.success :name="__('ui.' . $status)" />
                @break

                @case(App\Models\User::INACTIVE_STATUS)
                    <x-dashboard.badges.light :name="__('ui.' . $status)" />
                @break
            @endswitch
        </div>
        <div x-show="edit" class="flex flex-col gap-2">
            <x-dashboard.inputs.checkbox
                type="radio"
                id="user_pending_account_status"
                wire:model.live="account_status"
                :title="__('ui.' . App\Models\User::PENDING_STATUS)"
                value="{{ App\Models\User::PENDING_STATUS }}" />

            <x-dashboard.inputs.checkbox
                type="radio"
                id="user_active_account_status"
                wire:model.live="account_status"
                :title="__('ui.' . App\Models\User::ACTIVE_STATUS)"
                value="{{ App\Models\User::ACTIVE_STATUS }}" />

            <x-dashboard.inputs.checkbox
                type="radio"
                id="user_inactive_account_status"
                wire:model.live="account_status"
                :title="__('ui.' . App\Models\User::INACTIVE_STATUS)"
                value="{{ App\Models\User::INACTIVE_STATUS }}" />
        </div>
    </div>

    <!-- Rating -->
    <x-dashboard.info-label :name="__('ui.rating')">
        <x-dashboard.rating.inline class="w-4 h-4" :value="$user->reviews_avg_rating" />
    </x-dashboard.info-label>

    <!-- Residence name -->
    <x-dashboard.info-label
        :name="__('validation.attributes.residence_name')"
        :value="$user?->residence_name ?? __('ui.none')" />

    <!-- Residence number -->
    <x-dashboard.info-label
        :name="__('validation.attributes.residence_number')"
        :value="$user?->residence_number ?? __('ui.none')" />

    <!-- Residence image -->
    <x-dashboard.info-label :name="__('validation.attributes.residence_image')">
        @isset($residenceImage)
            <a href="{{ $residenceImage }}" target="_blank" class="text-brand-400 hover:text-brand-500">{{ __('ui.view_attachment') }}</a>
        @else
            {{ __('ui.none') }}
        @endisset
    </x-dashboard.info-label>

    <!-- Bank name -->
    <x-dashboard.info-label
        :name="__('validation.attributes.bank_name')"
        :value="$user?->bank_name" />

    <!-- IBAN -->
    <x-dashboard.info-label
        :name="__('validation.attributes.iban')"
        :value="$user?->iban" />

    <!-- Categories -->
    <x-dashboard.info-label :name="__('validation.attributes.categories')" class="flex flex-wrap gap-2 items-center">

        @forelse ($categories as $categoryName)
            <x-dashboard.badges.success :name="$categoryName" />
        @empty
            <x-dashboard.badges.light :name="__('ui.none')" />
        @endforelse

    </x-dashboard.info-label>

    @if ($user->entity_type != App\Models\User::INDIVIDUAL_ENTITY_TYPE)
        <!-- Commercial registration number -->
        <x-dashboard.info-label
            :name="__('validation.attributes.commercial_registration_number')"
            :value="$user?->commercial_registration_number" />

        <!-- Tax registration number -->
        <x-dashboard.info-label
            :name="__('validation.attributes.tax_registration_number')"
            :value="$user?->tax_registration_number ?? __('ui.none')" />

        <!-- Commercial registration image -->
        <x-dashboard.info-label :name="__('validation.attributes.commercial_registration_image')">
            @isset($commercialRegistrationImage)
                <a href="{{ $commercialRegistrationImage }}" target="_blank" class="text-brand-400 hover:text-brand-500">{{ __('ui.view_attachment') }}</a>
            @else
                {{ __('ui.none') }}
            @endisset
        </x-dashboard.info-label>

        <!-- National address image -->
        <x-dashboard.info-label :name="__('validation.attributes.national_address_image')">
            @isset($nationalAddressImage)
                <a href="{{ $nationalAddressImage }}" target="_blank" class="text-brand-400 hover:text-brand-500">{{ __('ui.view_attachment') }}</a>
            @else
                {{ __('ui.none') }}
            @endisset
        </x-dashboard.info-label>
    @endif

    @if (in_array($user->entity_type, [\App\Models\User::INSTITUTION_ENTITY_TYPE, \App\Models\User::COMPANY_ENTITY_TYPE]))
        <!-- Institution Members Section -->
        <div class="col-span-full space-y-4">

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Total Orders -->
                <div class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ __('ui.total_institution_orders') }}</p>
                        <p class="text-lg font-bold text-blue-700 dark:text-blue-300">{{ $user->members->sum('completed_orders') }}</p>
                    </div>
                </div>

                <!-- Total Earnings -->
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800">
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-800 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ __('ui.total_institution_earnings') }}</p>
                        <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($user->members->sum('total_earnings') ?? 0, config('app.decimal_places')) }} {{ __('ui.currency') }}</p>
                    </div>
                </div>

                <!-- Balance -->
                <div class="flex items-center gap-3 p-4 rounded-xl bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800">
                    <div class="w-10 h-10 bg-violet-100 dark:bg-violet-800 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-600 dark:text-violet-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2" /><line x1="2" y1="10" x2="22" y2="10" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-violet-600 dark:text-violet-400">{{ __('ui.balance') }}</p>
                        <p class="text-lg font-bold text-violet-700 dark:text-violet-300">{{ number_format($user->balance, config('app.decimal_places')) }} {{ __('ui.currency') }}</p>
                    </div>
                </div>
            </div>

            <!-- Members Header -->
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('ui.members', ['count' => $user->members->count()]) }}</span>
                </div>
                @if ($user->members->count())
                    <button wire:click="exportMembers" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm4 18H6V4h7v5h5zm-5.1-5.4L11 12.5l1.9-2.1H15l-2.8 3l2.8 3h-2.1l-1.9-2.1L9.1 16.4H7l2.8-3l-2.8-3h2.1z" /></svg>
                        {{ __('ui.export') }}
                    </button>
                @endif
            </div>

            <!-- Member Cards Grid -->
            @if ($user->members->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($user->members as $member)
                        <button wire:click="$dispatch('show-result', { id: {{ $member->id }} })"
                            class="flex flex-col p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-md transition-all cursor-pointer text-start group">

                            <!-- Member Header -->
                            <div class="flex items-center gap-3 mb-3">
                                <div class="relative">
                                    <img src="{{ $member->getAvatarUrl('sm') }}" alt="{{ $member->name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 group-hover:ring-brand-200 dark:group-hover:ring-brand-700 transition-all" />
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white dark:border-gray-800 {{ $member->status === 'active' ? 'bg-green-500' : ($member->status === 'pending' ? 'bg-amber-500' : 'bg-gray-400') }}"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-400 font-mono" dir="ltr">{{ $member->phone }}</p>
                                </div>
                            </div>

                            <!-- Member Stats -->
                            <div class="flex items-center gap-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                                    </svg>
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $member->completed_orders ?? 0 }} {{ __('ui.member_completed_orders') }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ number_format($member->total_earnings ?? 0, config('app.decimal_places')) }}</span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                    <svg class="w-10 h-10 mb-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><line x1="17" y1="11" x2="22" y2="11" />
                    </svg>
                    <p class="text-sm">{{ __('ui.no_results') }}</p>
                </div>
            @endif
        </div>
    @endif

</div>
