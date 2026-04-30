<aside x-bind:class="sidebarToggle ? dir === 'rtl' ? 'translate-x-0 lg:w-[85px]' : 'translate-x-0 lg:w-[85px]' : dir === 'rtl' ? 'translate-x-full' : '-translate-x-full'" class="sidebar z-9999 fixed start-0 top-0 flex h-screen w-[290px] flex-col overflow-y-hidden border-e border-gray-200 bg-white px-5 lg:static lg:translate-x-0 dark:border-gray-800 dark:bg-black">
    <!-- SIDEBAR HEADER -->
    <div x-bind:class="sidebarToggle ? 'justify-center' : 'justify-between'" class="sidebar-header flex items-center gap-2 pb-7 pt-8">
        <a href="{{ route('home') }}">
            <span class="logo" x-bind:class="sidebarToggle ? 'hidden' : ''">
                <x-app-logo class="max-h-8" />
            </span>

            <img class="logo-icon max-h-8" x-bind:class="sidebarToggle ? 'lg:block' : 'hidden'" src="{{ Cache::get('icon') }}" alt="Logo" />
        </a>
    </div>
    <!-- SIDEBAR HEADER -->

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <!-- Sidebar Menu -->
        <x-dashboard.nav />
        <!-- Sidebar Menu -->
    </div>
</aside>
