<div>
    <label class="flex items-center cursor-pointer">
        <input x-model="switcherToggle" @change="$wire.toggleTrash" type="checkbox" value="" class="sr-only peer">
        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600 dark:peer-checked:bg-brand-600"></div>
        <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-400">{{ $name }}</span>
    </label>
</div>
