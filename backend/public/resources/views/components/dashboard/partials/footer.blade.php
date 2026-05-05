<footer class="h-[60px] border-t border-gray-200 dark:border-gray-800">
    <p class="bg-white dark:bg-black p-4 text-gray-400 dark:text-gray-500 text-sm flex flex-wrap gap-1 justify-between w-full">
        <span>
            {{ __('ui.copyright') }} &copy; {{ date('Y') }} {{ $name }}.
        </span>
        <span>
            {{ __('ui.developed_by') }}
            <a href="https://www.facebook.com/profile.php?id=61587146045817" target="_blank" rel="nofollow" class="text-brand-400 hover:text-brand-500 font-normal">
                {{ __('ui.seem') }}
            </a>
        </span>
    </p>
</footer>
