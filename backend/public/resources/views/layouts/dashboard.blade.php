<!doctype html>
<html lang="{{ $locale }}" dir="{{ $dir }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    @vite(['resources/css/app.css', 'resources/css/dashboard.css'])

    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="{{ $app->description }}">

    <link rel="icon" href="{{ $app->icon }}">
    <link rel="apple-touch-icon" href="{{ $app->icon }}">
    <link rel="apple-touch-startup-image" href="{{ $app->icon }}">

    <title>{{ $title }}</title>

    @livewireStyles
</head>

<body x-data="{ page: '{{ $page }}', 'loaded': true, 'stickyMenu': false, 'darkMode': false, 'sidebarToggle': false, 'scrollTop': false, 'dir': '{{ $dir }}' }"
    x-init="darkMode = JSON.parse(localStorage.getItem('darkMode'));
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
    sidebarToggle = JSON.parse(localStorage.getItem('sidebarToggle'));
    $watch('sidebarToggle', value => localStorage.setItem('sidebarToggle', JSON.stringify(value)));"
    :class="{ 'dark bg-gray-900': darkMode === true }">
    <!-- ===== Preloader Start ===== -->
    @include('../dashboard/partials/preloader')
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @include('../dashboard/partials/sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- Small Device Overlay Start -->
            @include('../dashboard/partials/overlay')
            <!-- Small Device Overlay End -->

            <!-- ===== Header Start ===== -->
            <x-dashboard.partials.header />
            <!-- ===== Header End ===== -->

            <!-- ===== Main Content Start ===== -->
            <main>
                <div class="p-4 md:p-6 min-h-[calc(100dvh-7rem)]">
                    {{ $slot }}
                </div>
            </main>
            <!-- ===== Main Content End ===== -->

            <!-- ===== Footer Start ===== -->
            <x-dashboard.partials.footer :name="$app->name" />
            <!-- ===== Footer End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

    @livewireScriptConfig

    @vite(['resources/js/dashboard.js', 'resources/js/app.js'])
</body>

</html>
