<x-layouts.dashboard page="edit_user">

    <x-dashboard.breadcrumb :page="__('ui.edit_user')">
        @can('viewAny', App\Models\User::class)
            @if ($isServiceProvider)
                <x-dashboard.breadcrumb-back
                    :url="route('dashboard.users.service_providers')"
                    :name="__('ui.view_service_providers')" />
            @else
                <x-dashboard.breadcrumb-back
                    :url="route('dashboard.users.index')"
                    :name="__('ui.view_users')" />
            @endif
        @endcan
    </x-dashboard.breadcrumb>

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
        <form
            x-data="{ loading: false }"
            x-on:submit="loading = true"
            method="POST"
            action="{{ route('dashboard.users.update', ['user' => $user->id]) }}"
            enctype="multipart/form-data"
            class="p-4 flex flex-col gap-5">

            @method('PUT')
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl">

                {{-- ═══ Basic Information ═══ --}}
                <h3 class="col-span-full text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                    {{ __('ui.basic_information') }}
                </h3>

                <!-- Image -->
                <div class="col-span-full">
                    <x-dashboard.inputs.file.single-image
                        class="w-48"
                        preview-class="w-48 h-48"
                        id="image"
                        name="image"
                        :image-url="$avatar"
                        accept=".webp, .png, .jpg, .jpeg" />
                </div>

                <!-- Name -->
                <x-dashboard.inputs.default
                    name="name"
                    :value="$user->name"
                    :required="true" />

                <!-- Phone -->
                <x-dashboard.inputs.default
                    name="phone"
                    :value="$user->phone"
                    :required="true" />

                <!-- Password -->
                <x-dashboard.inputs.password
                    name="password" />

                <!-- Role -->
                <x-dashboard.inputs.select
                    label="role"
                    name="roles[]"
                    :children="$roles"
                    child-key="name"
                    :selected="old('roles', $userRoles)" />

                @if ($isServiceProvider)
                    {{-- ═══ Service Provider Information ═══ --}}
                    <h3 class="col-span-full text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mt-4">
                        {{ __('ui.service_provider') }}
                    </h3>

                    <!-- Status -->
                    <div>
                        <x-dashboard.label name="status" for="status" :required="false" />
                        <select name="status" id="status"
                            class="dark:bg-dark-900 focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach (App\Models\User::AVAILABLE_STATUS_TYPES as $status)
                                <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>
                                    {{ __('ui.' . $status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <x-dashboard.inputs.error :message="$message" /> @enderror
                    </div>

                    <!-- Entity Type -->
                    <div>
                        <x-dashboard.label name="entity_type" for="entity_type" :required="false" />
                        <select name="entity_type" id="entity_type"
                            class="dark:bg-dark-900 focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach (App\Models\User::AVAILABLE_ENTITY_TYPES as $type)
                                <option value="{{ $type }}" @selected(old('entity_type', $user->entity_type) === $type)>
                                    {{ __('ui.' . $type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('entity_type') <x-dashboard.inputs.error :message="$message" /> @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <x-dashboard.label name="city_id" for="city_id" :required="false" />
                        <select name="city_id" id="city_id"
                            class="dark:bg-dark-900 focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">{{ __('ui.select_city') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected(old('city_id', $user->city_id) == $city->id)>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id') <x-dashboard.inputs.error :message="$message" /> @enderror
                    </div>

                    <!-- Balance -->
                    <x-dashboard.inputs.default
                        name="balance"
                        type="number"
                        :value="old('balance', $user->balance ?? 0)" />

                    <!-- Residence Name -->
                    <x-dashboard.inputs.default
                        name="residence_name"
                        :value="old('residence_name', $user->residence_name)" />

                    <!-- Residence Number -->
                    <x-dashboard.inputs.default
                        name="residence_number"
                        :value="old('residence_number', $user->residence_number)" />

                    <!-- Bank Name -->
                    <x-dashboard.inputs.default
                        name="bank_name"
                        :value="old('bank_name', $user->bank_name)" />

                    <!-- IBAN -->
                    <x-dashboard.inputs.default
                        name="iban"
                        :value="old('iban', $user->iban)" />

                    <!-- Commercial Registration Number -->
                    <x-dashboard.inputs.default
                        name="commercial_registration_number"
                        :value="old('commercial_registration_number', $user->commercial_registration_number)" />

                    <!-- Tax Registration Number -->
                    <x-dashboard.inputs.default
                        name="tax_registration_number"
                        :value="old('tax_registration_number', $user->tax_registration_number)" />

                    {{-- ═══ Categories ═══ --}}
                    <div class="col-span-full">
                        <x-dashboard.inputs.select
                            label="categories"
                            id="edit-categories"
                            name="categories[]"
                            :children="$categories"
                            child-key="id"
                            :selected="old('categories', $userCategories)" />
                    </div>

                    {{-- ═══ Documents ═══ --}}
                    <h3 class="col-span-full text-base font-semibold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mt-4">
                        {{ __('ui.view_attachment') }}
                    </h3>

                    <!-- Residence Image -->
                    <div>
                        <x-dashboard.label name="residence_image" for="residence_image" :required="false" />
                        @if ($residenceImage)
                            <a href="{{ $residenceImage }}" target="_blank" class="block mb-2 text-sm text-brand-500 hover:text-brand-600">{{ __('ui.view_attachment') }} →</a>
                        @endif
                        <input type="file" name="residence_image" id="residence_image" accept=".webp,.png,.jpg,.jpeg"
                            class="block w-full text-sm text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-500/10 dark:file:text-brand-400" />
                        @error('residence_image') <x-dashboard.inputs.error :message="$message" /> @enderror
                    </div>

                    <!-- Commercial Registration Image -->
                    <div>
                        <x-dashboard.label name="commercial_registration_image" for="commercial_registration_image" :required="false" />
                        @if ($commercialRegistrationImage)
                            <a href="{{ $commercialRegistrationImage }}" target="_blank" class="block mb-2 text-sm text-brand-500 hover:text-brand-600">{{ __('ui.view_attachment') }} →</a>
                        @endif
                        <input type="file" name="commercial_registration_image" id="commercial_registration_image" accept=".webp,.png,.jpg,.jpeg"
                            class="block w-full text-sm text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-500/10 dark:file:text-brand-400" />
                        @error('commercial_registration_image') <x-dashboard.inputs.error :message="$message" /> @enderror
                    </div>
                @endif

            </div>

            <!-- Submit button -->
            <div class="max-w-4xl">
                <x-dashboard.buttons.primary :name="__('ui.update')" />
            </div>

        </form>
    </div>

    @vite('resources/js/file-input.js')

</x-layouts.dashboard>
