<div>
    <div x-data="previewImage({{ isset($imageUrl) ? "'$imageUrl'" : null }})" {{ $attributes->only(['class']) }}>

        <x-dashboard.label :for="$id" :name="$name" :required="$required ?? false" />

        <label for="{{ $id }}" class="cursor-pointer relative ">

            <div class="text-gray-400 z-10 absolute -top-2 -end-2 bg-white dark:bg-gray-900 dark:border-gray-700 border w-9 h-9 rounded-full hover:bg-gray-50 dark:hover:bg-gray-800 border-gray-200 flex items-center justify-center">
                <!-- material-symbols:edit-square-outline-sharp -->
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
                    <path fill="currentColor" d="M3 21V3h10.925l-2 2H5v14h14v-6.95l2-2V21zm6-6v-4.25L19.625.125L23.8 4.4L13.25 15zM21.025 4.4l-1.4-1.4zM11 13h1.4l5.8-5.8l-.7-.7l-.725-.7L11 11.575zm6.5-6.5l-.725-.7zl.7.7z" />
                </svg>
            </div>

            <div class="{{ $attributes->get('preview-class') }} dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 dark:bg-gray-900 rounded-md bg-gray-50 border border-gray-200 flex items-center justify-center overflow-hidden">

                <img x-show="imageUrl" :src="imageUrl" class="w-full h-full object-cover object-center">

                <div x-show="!imageUrl" class="text-gray-400 flex flex-col gap-3 items-center">
                    <!-- material-symbols:add-photo-alternate-rounded -->
                    <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
                        <path fill="currentColor" d="M5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h7.45q.5 0 .763.438t.062.937q-.125.4-.2.8T13 6q0 2.075 1.463 3.538T18 11q.425 0 .825-.075t.8-.2q.5-.175.938.075t.437.75V19q0 .825-.587 1.413T19 21zm1-4h12l-3.75-5l-3 4L9 13zm12-8q-.425 0-.712-.288T17 8V7h-1q-.425 0-.712-.288T15 6t.288-.712T16 5h1V4q0-.425.288-.712T18 3t.713.288T19 4v1h1q.425 0 .713.288T21 6t-.288.713T20 7h-1v1q0 .425-.288.713T18 9" />
                    </svg>
                    <div>{{ __('ui.upload_image') }}</div>
                </div>

            </div>

            <p class="mt-2 text-gray-500 text-sm text-center w-full">{{ __('ui.image_input_description') }}</p>

            @error($name)
                <x-dashboard.inputs.error :message="$message" />
            @enderror
        </label>

        <div class="mt-4 hidden">
            <input
                {{ $attributes->except(['required'])->merge([
                    'class' => 'cursor-pointer focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:me-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pe-3 file:ps-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400 ps-3',
                    'type' => 'file',
                    '@change' => 'fileChosen',
                ]) }}>
        </div>

    </div>
</div>
