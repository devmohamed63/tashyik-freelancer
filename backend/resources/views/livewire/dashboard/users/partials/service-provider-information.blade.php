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
        <!-- Members -->
        <div class="flex flex-col gap-3 col-span-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 border-dashed">
            <span class="text-gray-600 dark:text-gray-400">{{ __('ui.members', ['count' => $user->members->count()]) }}</span>

            <div class="flex flex-col gap-5 md:grid grid-cols-3">
                @foreach ($user->members as $member)
                    <button wire:click="$dispatch('show-result', { id: {{ $member->id }} })" class="w-fit inline-flex gap-1 items-center cursor-pointer group">
                        <span class="transition-colors group-hover:text-brand-500 text-gray-600 dark:text-gray-400 text-nowrap">{{ $member->name }}</span>
                        <!-- ic:round-remove-red-eye -->
                        <svg class="transition-colors group-hover:text-brand-500 text-gray-400 dark:text-gray-500 w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
                            <path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5M12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5s5 2.24 5 5s-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3s-1.34-3-3-3" />
                        </svg>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

</div>
