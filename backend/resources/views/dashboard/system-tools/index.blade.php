<x-layouts.dashboard page="system_tools">

    <x-dashboard.breadcrumb :page="__('ui.system_tools')" />

    @session('status')
        <div class="mb-5">
            <x-dashboard.alerts.success :title="$value" />
        </div>
    @endsession

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- Generate Sitemap Card --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-4 mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-6 w-6 text-brand-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m-1 17.93c-3.95-.49-7-3.85-7-7.93c0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41c0 2.08-.8 3.97-2.1 5.39"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('ui.generate_sitemap') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.generate_sitemap_description') }}</p>
                </div>
            </div>

            @if($sitemapLastGenerated)
                <div class="mb-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10s10-4.5 10-10S17.5 2 12 2m4.2 14.2L11 13V7h1.5v5.2l4.5 2.7z"/>
                        </svg>
                        <span>{{ __('ui.last_generated') }}: <strong>{{ \Carbon\Carbon::createFromTimestamp($sitemapLastGenerated)->diffForHumans() }}</strong></span>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-3 mb-4">
                @php
                    $sitemapFiles = ['index.xml', 'categories.xml', 'services.xml'];
                    $sitemapBaseUrl = rtrim(env('FRONTEND_URL'), '/') . '/sitemaps';
                @endphp
                @foreach($sitemapFiles as $file)
                    @if(file_exists(public_path("sitemaps/{$file}")))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                <path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19L21 7l-1.41-1.41z"/>
                            </svg>
                            {{ $file }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                <path fill="currentColor" d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12z"/>
                            </svg>
                            {{ $file }}
                        </span>
                    @endif
                @endforeach
            </div>

            {{-- Sitemap URLs --}}
            <div class="mb-4 rounded-lg border border-gray-100 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('ui.sitemap_urls') }}:</p>
                <div class="space-y-2">
                    @foreach($sitemapFiles as $file)
                        @if(file_exists(public_path("sitemaps/{$file}")))
                            <div class="flex items-center gap-2 rounded-md bg-white px-3 py-2 dark:bg-gray-900" x-data="{ copied: false }">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none">
                                    <path fill="currentColor" d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1M8 13h8v-2H8zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5"/>
                                </svg>
                                <code class="flex-1 truncate text-xs text-gray-600 dark:text-gray-300" dir="ltr">{{ $sitemapBaseUrl }}/{{ $file }}</code>
                                <button
                                    type="button"
                                    x-on:click="navigator.clipboard.writeText('{{ $sitemapBaseUrl }}/{{ $file }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="shrink-0 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors"
                                    :title="copied ? '{{ __('ui.copied') }}' : '{{ __('ui.copy_link') }}'">
                                    <svg x-show="!copied" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                        <path fill="currentColor" d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2m0 16H8V7h11z"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak class="h-4 w-4 text-green-500" viewBox="0 0 24 24" fill="none">
                                        <path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19L21 7l-1.41-1.41z"/>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <form action="{{ route('dashboard.system-tools.generate-sitemap') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition-colors">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path fill="currentColor" d="M17.65 6.35A7.96 7.96 0 0 0 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08A5.99 5.99 0 0 1 12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4z"/>
                    </svg>
                    {{ __('ui.generate_sitemap') }}
                </button>
            </form>
        </div>

        {{-- Clear Cache Card --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center gap-4 mb-4">
                <div style="width:48px;height:48px;background-color:#fff7ed;" class="flex items-center justify-center rounded-lg">
                    <svg style="width:24px;height:24px;color:#f97316;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path fill="currentColor" d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6zM8 9h8v10H8zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('ui.clear_cache') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.clear_cache_description') }}</p>
                </div>
            </div>

            <div class="mb-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('ui.clear_cache_includes') }}:</p>
                <ul class="mt-2 space-y-1">
                    @foreach(['Application Cache', 'Configuration Cache', 'Route Cache', 'View Cache'] as $cacheType)
                        <li class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="text-orange-400" style="width:14px;height:14px;min-width:14px;" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m-2 15l-5-5l1.41-1.41L10 14.17l7.59-7.59L19 8z"/>
                            </svg>
                            {{ $cacheType }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <form action="{{ route('dashboard.system-tools.clear-cache') }}" method="POST">
                @csrf
                <button type="submit" style="background-color:#f97316;" class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:opacity-90 transition-colors">
                    <svg style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path fill="currentColor" d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6zM8 9h8v10H8zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                    </svg>
                    {{ __('ui.clear_cache') }}
                </button>
            </form>
        </div>

    </div>

</x-layouts.dashboard>
