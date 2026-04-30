<form
    x-data="{ loading: false }"
    x-on:submit="loading = true"
    method="POST"
    action="{{ route('dashboard.settings.update_basic_information') }}"
    enctype="multipart/form-data"
    class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl">

    @method('PUT')
    @csrf

    <div class="col-span-full w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <!-- Icon -->
        <div>
            <x-dashboard.inputs.file.single-image
                class="w-32"
                preview-class="w-32 h-32"
                id="icon"
                name="icon"
                :image-url="$iconUrl"
                accept=".webp, .png, .jpg, .jpeg" />
        </div>

        <!-- Logo (Light mode) -->
        <div>
            <x-dashboard.inputs.file.single-image
                class="w-48"
                preview-class="w-48 asspect-video"
                id="logo-light-mode"
                name="light_mode_logo"
                :image-url="$lightModeLogoUrl"
                accept=".webp, .png, .jpg, .jpeg" />
        </div>

        <!-- Logo (Dark mode) -->
        <div>
            <x-dashboard.inputs.file.single-image
                class="w-48"
                preview-class="w-48 asspect-video"
                id="logo-dark-mode"
                name="dark_mode_logo"
                :image-url="$darkModeLogoUrl"
                accept=".webp, .png, .jpg, .jpeg" />
        </div>
    </div>

    <!-- Name ar -->
    <x-dashboard.inputs.default
        name="name"
        locale="ar"
        :value="$settings->getTranslation('name', 'ar')"
        id="basic-information-name-ar"
        :required="true" />

    <!-- Name en -->
    <x-dashboard.inputs.default
        name="name"
        locale="en"
        :value="$settings->getTranslation('name', 'en')"
        id="basic-information-name-en"
        :required="true" />

    <div class="col-span-full">
        <!-- Description ar -->
        <x-dashboard.inputs.textarea
            name="description"
            locale="ar"
            :value="$settings->getTranslation('description', 'ar')"
            id="basic-information-description-ar"
            rows="4"
            :required="true" />
    </div>

    <!-- Phone Number -->
    <x-dashboard.inputs.default
        name="phone_number"
        :value="$settings->phone_number"
        id="basic-information-phone_number"
        :required="true" />

    <!-- Whatsapp Link -->
    <x-dashboard.inputs.default
        name="whatsapp_link"
        :value="$settings->whatsapp_link"
        id="basic-information-whatsapp_link"
        :required="true" />

    <!-- Email -->
    <x-dashboard.inputs.default
        name="email"
        type="email"
        :value="$settings->email"
        id="basic-information-email"
        :required="true" />

    <!-- Submit button -->
    <div class="col-span-full">
        <x-dashboard.buttons.primary :name="__('ui.update')" />
    </div>

</form>
