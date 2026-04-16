<x-layouts.dashboard page="technician_map">

    {{-- Google Maps JS CSS --}}
    <style>
        .gm-style-iw { border-radius: 16px !important; padding: 0 !important; overflow: hidden !important; }
        .gm-style-iw-d { overflow: auto !important; padding: 0 !important; }
        .gm-style-iw-c { padding: 0 !important; border-radius: 16px !important; box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important; max-height: none !important;}
        .gm-style .gm-style-iw-tc::after { background: #fff !important; }
        div.gm-style-iw-chr { position: absolute; top: 10px; left: 10px; right: auto; z-index: 10; border-radius: 50%; opacity: 0.7; }

        /* ── Map ─────────────────────────────────────── */
        #technician-map {
            height: calc(100vh - 220px);
            min-height: 500px;
            border-radius: 0 0 1rem 1rem;
            z-index: 1;
        }
        .map-fullscreen #technician-map {
            position: fixed !important;
            inset: 0;
            height: 100vh !important;
            z-index: 99999;
            border-radius: 0;
        }

        /* ── Custom Markers ──────────────────────────── */
        .marker-pin {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .marker-pin .pin-core {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 3px 12px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }
        .marker-pin .pin-core svg {
            width: 16px;
            height: 16px;
            fill: #fff;
        }
        
        .marker-city {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border: 3px solid rgba(255,255,255,0.8);
            font-weight: 700;
            color: #fff;
        }

        /* ── Premium Popup ───────────────────────────── */
        .popup-card { direction: rtl; font-family: inherit; }
        .popup-header {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .popup-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            flex-shrink: 0;
        }
        .popup-name { font-weight: 700; font-size: 15px; color: #1f2937; margin-bottom: 2px; }
        .popup-type { font-size: 12px; color: #9ca3af; }
        .popup-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
        }
        .popup-badge.online_available { background: #22c55e; }
        .popup-badge.online_busy { background: #f97316; }
        .popup-badge.offline { background: #9ca3af; }

        .popup-body { padding: 12px 16px; }
        .popup-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 0;
            font-size: 13px;
            color: #6b7280;
        }
        .popup-row svg { width: 16px; height: 16px; color: #9ca3af; flex-shrink: 0; }

        .popup-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 8px 16px;
        }
        .cat-badge {
            background: #eef2ff;
            color: #4f46e5;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        .popup-actions {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid #f3f4f6;
            background: #f9fafb;
        }
        .popup-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }
        .popup-btn-call {
            background: #eff6ff;
            color: #3b82f6;
        }
        .popup-btn-call:hover { background: #dbeafe; }
        .popup-btn-whatsapp {
            background: #f0fdf4;
            color: #22c55e;
        }
        .popup-btn-whatsapp:hover { background: #dcfce7; }

        /* ── Side Panel ──────────────────────────────── */
        .side-panel {
            width: 340px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 0;
            background: #fff;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            max-height: calc(100vh - 220px);
            min-height: 500px;
        }
        .dark .side-panel {
            background: rgba(255,255,255,0.03);
            border-color: #374151;
        }
        .side-panel-header {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .side-panel-header { border-color: #374151; }

        .side-panel-list {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .side-panel-list::-webkit-scrollbar { width: 4px; }
        .side-panel-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

        .tech-list-item, .city-list-item, .alert-list-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.15s;
            border-bottom: 1px solid #f9fafb;
        }
        .tech-list-item:hover, .city-list-item:hover, .alert-list-item:hover { background: #f3f4f6; }
        .dark .tech-list-item:hover, .dark .city-list-item:hover, .dark .alert-list-item:hover { background: rgba(255,255,255,0.05); }
        .dark .tech-list-item, .dark .city-list-item, .dark .alert-list-item { border-color: #1f2937; }

        .tech-list-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        .tech-list-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
        }

        /* ── Tabs ────────────────────────────────────── */
        .panel-tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .dark .panel-tabs { border-color: #374151; background: #1f2937; }
        .panel-tab {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }
        .panel-tab:hover { color: #374151; }
        .panel-tab.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
            background: #fff;
        }
        .dark .panel-tab.active { background: transparent; color: #818cf8; border-bottom-color: #818cf8; }

        /* ── Action Buttons ──────────────────────────── */
        .btn-action-small {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            background: #4f46e5;
            color: #fff;
            transition: all 0.2s;
            text-align: center;
        }
        .btn-action-small:hover { background: #4338ca; }

        /* ── Stat Mini Cards ─────────────────────────── */
        .stat-mini {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .stat-mini:hover { transform: translateY(-1px); }
        .stat-mini-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .stat-mini-icon svg { width: 18px; height: 18px; }

        /* ── Control Buttons ─────────────────────────── */
        .map-control-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .map-control-btn:hover { background: #f3f4f6; transform: scale(1.05); }
        .map-control-btn.active { color: #ef4444; border-color: #ef4444; background: #fef2f2; }
        .map-control-btn svg { width: 18px; height: 18px; }

        /* ── Responsive ──────────────────────────────── */
        @media (max-width: 1024px) {
            .side-panel { display: none; }
        }

        .live-dot {
            width: 8px; height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: live-pulse 2s ease-in-out infinite;
        }
        @keyframes live-pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(34,197,94,0.6); }
            50% { opacity: 0.8; box-shadow: 0 0 0 6px rgba(34,197,94,0); }
        }
    </style>

    <div class="flex flex-col gap-4" x-data="technicianMap()" x-init="init()" :class="{ 'map-fullscreen': isFullscreen }">

        {{-- ══════════════════════════════════════════════ --}}
        {{-- Header Bar                                     --}}
        {{-- ══════════════════════════════════════════════ --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/20">
                    <!-- material-symbols:map -->
                    <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M14.5 7a3.5 3.5 0 1 0-3.5 3.5A3.5 3.5 0 0 0 14.5 7M11 2a5 5 0 0 1 5 5c0 3.87-5 9-5 9S6 10.87 6 7a5 5 0 0 1 5-5m7.5 7A2.5 2.5 0 1 0 16 11.5A2.5 2.5 0 0 0 18.5 9M16 6a4 4 0 0 1 4 4c0 2.8-4 7-4 7s-1.42-1.49-2.53-3.21A7 7 0 0 0 16 6M3.5 9A2.5 2.5 0 1 1 6 11.5A2.5 2.5 0 0 1 3.5 9M6 6a4 4 0 0 0-4 4c0 2.8 4 7 4 7s1.42-1.49 2.53-3.21A7 7 0 0 1 6 6"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        {{ __('ui.technician_map') }}
                    </h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('ui.technician_map_subtitle') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Search --}}
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.300ms="filterLists()"
                           placeholder="{{ __('ui.search_technician') }}"
                           class="w-64 rounded-xl border border-gray-200 bg-white pe-4 ps-10 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14"/></svg>
                </div>

                {{-- Live indicator --}}
                <div class="flex items-center gap-2 text-xs text-gray-400 bg-gray-50 dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="live-dot"></div>
                    <span>LIVE</span>
                </div>

                {{-- Fullscreen toggle --}}
                <button @click="toggleFullscreen()" class="map-control-btn text-gray-600" title="Fullscreen">
                    <svg x-show="!isFullscreen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7 14H5v5h5v-2H7zm-2-4h2V7h3V5H5zm12 7h-3v2h5v-5h-2zM14 5v2h3v3h2V5z"/></svg>
                    <svg x-show="isFullscreen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M5 16h3v3h2v-5H5zm3-8H5v2h5V5H8zm6 11h2v-3h3v-2h-5zm2-11V5h-2v5h5V8z"/></svg>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════ --}}
        {{-- Main Content: Side Panel + Map                 --}}
        {{-- ══════════════════════════════════════════════ --}}
        <div class="flex gap-4">

            {{-- ── Side Panel ─────────────────────────── --}}
            <div class="side-panel">

                {{-- Tabs --}}
                <div class="panel-tabs">
                    <div @click="activeTab = 'technicians'" :class="{'active': activeTab === 'technicians'}" class="panel-tab">{{ __('ui.technicians_tab') }}</div>
                    <div @click="activeTab = 'cities'" :class="{'active': activeTab === 'cities'}" class="panel-tab">{{ __('ui.cities_tab') }}</div>
                    <div @click="activeTab = 'alerts'" :class="{'active': activeTab === 'alerts'}" class="panel-tab relative">
                        {{ __('ui.alerts_tab') }}
                        <span x-show="insights.alerts.length > 0" class="absolute top-1.5 start-2 flex h-2 w-2 rounded-full bg-red-500"></span>
                    </div>
                </div>

                {{-- Tab Content: Technicians --}}
                <div x-show="activeTab === 'technicians'" class="flex flex-col h-full overflow-hidden">
                    {{-- Mini Stats --}}
                    <div class="grid grid-cols-2 gap-2 p-3">
                        <div class="stat-mini bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-500/5 dark:to-emerald-500/5">
                            <div class="stat-mini-icon bg-green-500/10">
                                <svg class="text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8z"/></svg>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="stats.online_available"></p>
                                <p class="text-[10px] text-gray-500 leading-tight">{{ __('ui.online_available') }}</p>
                            </div>
                        </div>

                        <div class="stat-mini bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-500/5 dark:to-amber-500/5">
                            <div class="stat-mini-icon bg-orange-500/10">
                                <svg class="text-orange-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m-1 5h2v6h-2zm0 8h2v2h-2z"/></svg>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="stats.online_busy"></p>
                                <p class="text-[10px] text-gray-500 leading-tight">{{ __('ui.online_busy') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="side-panel-header">
                        <div class="flex flex-col gap-2">
                            <select x-model="cityFilter" @change="fetchData()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="">🏙️ {{ __('ui.all_cities') }}</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                            <div class="grid grid-cols-2 gap-2">
                                <select x-model="categoryFilter" @change="fetchData()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">🏷️ {{ __('ui.filter_by_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <select x-model="statusFilter" @change="fetchData()" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="">📊 {{ __('ui.all_statuses') }}</option>
                                    <option value="online">🟢 {{ __('ui.online_available') }}</option>
                                    <option value="offline">⚫ {{ __('ui.offline') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Technician List --}}
                    <div class="side-panel-list">
                        <template x-for="tech in filteredTechnicians" :key="tech.id">
                            <div class="tech-list-item" @click="flyToTechnician(tech)">
                                <div class="relative">
                                    <img :src="tech.avatar" :alt="tech.name" class="tech-list-avatar" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22><circle cx=%2212%22 cy=%2212%22 r=%2212%22 fill=%22%23e5e7eb%22/><path fill=%22%239ca3af%22 d=%22M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4m0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4%22/></svg>'">
                                    <div class="tech-list-status absolute -bottom-0.5 -end-0.5"
                                         :class="{
                                             'bg-green-500': tech.status === 'online_available',
                                             'bg-orange-500': tech.status === 'online_busy',
                                             'bg-gray-400': tech.status === 'offline'
                                         }"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate" x-text="tech.name"></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[11px] text-gray-400 truncate" x-text="tech.city"></span>
                                        <span class="text-[10px] text-gray-300">•</span>
                                        <span class="text-[11px] text-gray-400 truncate" x-text="tech.last_seen_at"></span>
                                    </div>
                                </div>
                                <span class="popup-badge text-[10px] flex-shrink-0" :class="tech.status" x-text="statusLabels[tech.status]"></span>
                            </div>
                        </template>

                        <div x-show="filteredTechnicians.length === 0" class="flex flex-col items-center justify-center py-12 px-4 text-center">
                            <svg class="w-12 h-12 text-gray-200 mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7m0 9.5a2.5 2.5 0 0 1 0-5a2.5 2.5 0 0 1 0 5"/></svg>
                            <p class="text-sm text-gray-400">{{ __('ui.no_results') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Tab Content: Cities --}}
                <div x-show="activeTab === 'cities'" class="flex flex-col h-full overflow-hidden" x-cloak>
                    <div class="side-panel-list pt-2">
                        <template x-for="city in insights.cities_overview" :key="city.id">
                            <div class="flex flex-col border-b border-gray-100 dark:border-gray-800">
                                <div class="city-list-item" @click="toggleCityDetail(city.id)">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-sm"
                                         :class="city.coverage_status === 'good' ? 'bg-green-500' : (city.coverage_status === 'warning' ? 'bg-yellow-500' : 'bg-red-500')">
                                        <span x-text="city.coverage_percentage + '%'"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white truncate" x-text="city.name"></p>
                                        </div>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-[11px] text-gray-500 flex items-center gap-1">
                                                <svg class="w-3 h-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4s-4 1.79-4 4s1.79 4 4 4m0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4"/></svg>
                                                <span x-text="city.total_providers"></span>
                                            </span>
                                            <span class="text-[11px] text-gray-500 flex items-center gap-1" title="{{ __('ui.demand_supply') }}">
                                                <svg class="w-3 h-3 text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M16 6l2.29 2.29l-4.88 4.88l-4-4L2 16.59L3.41 18l6-6l4 4l6.3-6.29L22 12V6z"/></svg>
                                                <span x-text="city.demand_supply_ratio + ' {{ __('ui.orders_per_provider') }}'"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': expandedCity === city.id}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6l-6-6z"/></svg>
                                    </div>
                                </div>

                                {{-- City Detail Drawer --}}
                                <div x-show="expandedCity === city.id" class="bg-gray-50 dark:bg-gray-800/50 p-4 text-sm" x-collapse>
                                    <div class="mb-3 flex justify-between text-[11px] text-gray-500">
                                        <span>{{ __('ui.categories_covered') }}: <strong x-text="city.categories_covered + '/' + city.categories_total" class="text-gray-800 dark:text-gray-200"></strong></span>
                                        <span>{{ __('ui.total_orders_7d') }}: <strong x-text="city.total_orders_7d" class="text-gray-800 dark:text-gray-200"></strong></span>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <template x-for="cat in city.categories_breakdown" :key="cat.id">
                                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded border border-gray-100 dark:border-gray-700">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full" :class="cat.status === 'good' ? 'bg-green-500' : (cat.status === 'warning' ? 'bg-yellow-500' : 'bg-red-500')"></span>
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300" x-text="cat.name"></span>
                                                </div>
                                                <div class="flex flex-col items-end">
                                                    <span class="text-[10px] text-gray-500" x-text="cat.providers_count + ' {{ __('ui.providers_in_category') }}'"></span>
                                                    <span x-show="cat.stale_orders_count > 0" class="text-[9px] text-red-500 font-bold" x-text="cat.stale_orders_count + ' {{ __('ui.uncovered_orders') }}'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Tab Content: Alerts --}}
                <div x-show="activeTab === 'alerts'" class="flex flex-col h-full overflow-hidden bg-gray-50 dark:bg-gray-900/50" x-cloak>
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-white dark:bg-gray-800">
                        <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m1 15h-2v-2h2v2m0-4h-2V7h2v6"/></svg>
                            {{ __('ui.alerts') }}
                        </h3>
                        <span class="text-xs bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded-full" x-text="insights.alerts.length + ' {{ __('ui.shortage') }}'"></span>
                    </div>
                    
                    <div class="side-panel-list p-3 space-y-3">
                        <template x-for="(alert, index) in insights.alerts" :key="index">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-red-200 dark:border-red-900/50 p-4 shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 start-0 w-1 h-full bg-red-500"></div>
                                <div class="flex gap-3">
                                    <div class="mt-0.5">
                                        <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11 15h2v2h-2v-2m0-8h2v6h-2V7m1-5C6.47 2 2 6.5 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m0 18a8 8 0 0 1-8-8a8 8 0 0 1 8-8a8 8 0 0 1 8 8a8 8 0 0 1-8 8"/></svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold text-gray-800 dark:text-white" x-text="alert.city_name + ' - ' + alert.category_name"></h4>
                                        <p class="text-[11px] text-red-600 mt-1 dark:text-red-400">{{ __('ui.category_enabled_no_providers') }}</p>
                                        
                                        <div class="mt-3 flex justify-end">
                                            <a :href="'{{ route('dashboard.users.create_service_provider') }}?city_id=' + alert.city_id" class="btn-action-small">
                                                {{ __('ui.action_add_provider') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="insights.alerts.length === 0" class="flex flex-col items-center justify-center py-10 opacity-60">
                            <svg class="w-16 h-16 text-green-500 mb-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22a10 10 0 1 1 0-20a10 10 0 0 1 0 20m0-2a8 8 0 1 0 0-16a8 8 0 0 0 0 16m-2.3-8.7l1.3 1.29l3.3-3.3a1 1 0 0 1 1.4 1.42l-4 4a1 1 0 0 1-1.4 0l-2-2a1 1 0 0 1 1.4-1.42"/></svg>
                            <p class="text-sm text-gray-500 font-medium">{{ __('ui.no_shortage_alerts') }}</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Map Container ──────────────────────── --}}
            <div class="flex-1 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm relative bg-white dark:bg-white/[0.03]">

                {{-- Map Top Bar --}}
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm">
                    {{-- Legend --}}
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border-[2.5px] border-green-500 inline-block"></span>
                            {{ __('ui.online_available') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border-[2.5px] border-orange-500 inline-block"></span>
                            {{ __('ui.online_busy') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full border-[2.5px] border-gray-400 inline-block"></span>
                            {{ __('ui.offline') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-pink-500 inline-block"></span>
                            {{ __('ui.pending_orders') ?? 'الطلبات المعلقة' }}
                        </span>
                    </div>

                    {{-- Map Controls --}}
                    <div class="flex items-center gap-2">
                        <button @click="toggleOrders()" class="map-control-btn text-gray-600" :class="{'active': showOrders}" title="{{ __('ui.toggle_orders') ?? 'إظهار الطلبات' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 21H6c-1.11 0-2-.89-2-2V5c0-1.11.89-2 2-2h12c1.11 0 2 .89 2 2v7.83c-.6-.42-1.28-.73-2-.92V5H6v14h6.54c.16.73.47 1.41.87 2m-5-8h10v-2H7zm0-4h10V7H7zm11 13a4.5 4.5 0 0 1-4.5-4.5c0-2.49 2.01-4.5 4.5-4.5H19v-2h-2v2h-1c-.55 0-1-.45-1-1s.45-1 1-1h1c1.1 0 2 .9 2 2v2h2v-2h1c.55 0 1 .45 1 1s-.45 1-1 1h-1c-1.1 0-2-.9-2-2v-2h-2v2z"/></svg>
                        </button>
                        <button @click="toggleHeatmap()" class="map-control-btn text-gray-600" :class="{'active': heatmapVisible}" title="{{ __('ui.heatmap_toggle') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3s-3-1.34-3-3s1.34-3 3-3m0 14.2c-2.5 0-4.71-1.28-6-3.22c.03-1.99 4-3.08 6-3.08s5.97 1.09 6 3.08c-1.29 1.94-3.5 3.22-6 3.22"/></svg>
                        </button>
                        <button @click="resetView()" class="map-control-btn text-gray-600" title="{{ __('ui.reset_view') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4s4-1.79 4-4s-1.79-4-4-4m8.94 3A8.994 8.994 0 0 0 13 3.06V1h-2v2.06A8.994 8.994 0 0 0 3.06 11H1v2h2.06A8.994 8.994 0 0 0 11 20.94V23h2v-2.06A8.994 8.994 0 0 0 20.94 13H23v-2zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7s7 3.13 7 7s-3.13 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <div id="technician-map"></div>
            </div>

        </div>
    </div>

    {{-- Google Maps JS --}}
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
    <script>
        window.initMap = function() {
            // Google Maps is ready, Alpine's init() will detect it automatically via waitForGoogle
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=marker,visualization&callback=initMap" async defer></script>
    <script>
        function technicianMap() {
            return {
                map: null,
                markerGroup: null,
                markers: {},
                cityMarkers: [],
                heatmapLayer: null,
                infoWindow: null,
                activeTab: 'technicians', // technicians, cities, alerts
                expandedCity: null,
                technicians: [],
                filteredTechnicians: [],
                pendingOrders: [],
                orderMarkers: {},
                showOrders: true,
                insights: {
                    cities_overview: [],
                    alerts: []
                },
                heatmapDataPoints: [],
                heatmapVisible: false,
                cityFilter: '',
                categoryFilter: '',
                statusFilter: '',
                searchQuery: '',
                pollInterval: null,
                isFullscreen: false,
                _lastFilterKey: '__initial__', // forces first fetchData to be a full rebuild
                _isFetching: false,             // prevents overlapping poll requests
                _fetchController: null,         // AbortController for filter-change cancellation
                stats: {
                    online_available: {{ $stats['online_available'] }},
                    online_busy: {{ $stats['online_busy'] }},
                    offline: {{ $stats['offline'] }},
                    total: {{ $stats['total'] }},
                },
                statusLabels: {
                    online_available: '{{ __("ui.online_available") }}',
                    online_busy: '{{ __("ui.online_busy") }}',
                    offline: '{{ __("ui.offline") }}',
                },

                _markerColor: '#7c3aed', // purple for all technicians

                _statusColors: {
                    online_available: '#22c55e',
                    online_busy: '#f97316',
                    offline: '#9ca3af',
                },

                /**
                 * Build marker pin SVG with dual color info:
                 * - catColor: main pin body (represents category)
                 * - statusColor: inner ring around the person icon (represents online status)
                 */
                _markerSvg(catColor, statusColor) {
                    return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="48" viewBox="0 0 36 48">
                            <defs>
                                <filter id="s" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity="0.3"/>
                                </filter>
                            </defs>
                            <path d="M18 0C8.06 0 0 8.06 0 18c0 13.5 18 30 18 30s18-16.5 18-30C36 8.06 27.94 0 18 0z" fill="${catColor}" filter="url(#s)"/>
                            <circle cx="18" cy="16" r="11" fill="${statusColor}" opacity="0.9"/>
                            <circle cx="18" cy="16" r="9" fill="white" opacity="0.95"/>
                            <path d="M18 12a3.5 3.5 0 100 7 3.5 3.5 0 000-7zm0 9c-2.33 0-7 1.17-7 3.5V26h14v-1.5c0-2.33-4.67-3.5-7-3.5z" fill="${catColor}" opacity="0.85"/>
                        </svg>
                    `)}`;
                },

                init() {
                    const waitForGoogle = () => {
                        if (typeof google !== 'undefined' && google.maps && google.maps.visualization) {
                            this._initMap();
                        } else {
                            setTimeout(waitForGoogle, 100);
                        }
                    };
                    waitForGoogle();
                },

                _initMap() {
                    try {
                        this.map = new google.maps.Map(document.getElementById('technician-map'), {
                            center: { lat: 24.0, lng: 45.0 },
                            zoom: 6,
                            disableDefaultUI: true,
                            zoomControl: true,
                            gestureHandling: 'greedy',
                            styles: [
                                { featureType: 'poi', stylers: [{ visibility: 'off' }] },
                                { featureType: 'transit', stylers: [{ visibility: 'off' }] },
                            ],
                        });

                        this.infoWindow = new google.maps.InfoWindow({ minWidth: 280, maxWidth: 350 });

                        // Note: markerGroup (clusterer) is created by syncMarkers on first fetchData

                        this.fetchData();
                        this.fetchInsights();
                        this.pollInterval = setInterval(() => {
                            this.fetchData();
                            this.fetchInsights();
                        }, 15000);
                        
                        this.$watch('activeTab', (val) => {
                            this.renderMapIcons();
                        });

                    } catch (err) {
                        console.error('[TechnicianMap] Init failed:', err);
                    }
                },

                async fetchData() {
                    const params = new URLSearchParams();
                    if (this.cityFilter) params.append('city_id', this.cityFilter);
                    if (this.categoryFilter) params.append('category_id', this.categoryFilter);
                    if (this.statusFilter) params.append('status', this.statusFilter);

                    const currentFilterKey = `${this.cityFilter}|${this.categoryFilter}|${this.statusFilter}`;
                    const filtersChanged = currentFilterKey !== this._lastFilterKey;
                    this._lastFilterKey = currentFilterKey;

                    if (filtersChanged) {
                        // Filter change → abort any in-flight request and proceed immediately
                        if (this._fetchController) this._fetchController.abort();
                    } else if (this._isFetching) {
                        // Poll → skip if a request is already in-flight
                        return;
                    }

                    this._isFetching = true;
                    this._fetchController = new AbortController();

                    try {
                        console.log('[MAP]', filtersChanged ? '🔄 FILTER →' : '⟳ poll →', params.toString() || '(no filters)');

                        const response = await fetch(
                            `{{ route('dashboard.technician-map.api') }}?${params.toString()}`,
                            { signal: this._fetchController.signal }
                        );
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        const data = await response.json();

                        console.log(`[MAP] ← ${data.technicians.length} technicians`, filtersChanged ? '→ REBUILD' : '');

                        this.stats = data.stats;
                        this.technicians = data.technicians;
                        this.pendingOrders = data.pending_orders || [];
                        this.filterLists();
                        if (this.activeTab === 'technicians') {
                            this.syncMarkers(data.technicians, filtersChanged);
                            this.syncOrderMarkers(this.pendingOrders);
                        }
                    } catch (error) {
                        if (error.name === 'AbortError') return;
                        console.error('[TechnicianMap] Fetch failed:', error);
                    } finally {
                        this._isFetching = false;
                    }
                },
                
                async fetchInsights() {
                    try {
                        const [insightsRes, heatmapRes] = await Promise.all([
                            fetch(`{{ route('dashboard.technician-map.city-insights') }}`),
                            fetch(`{{ route('dashboard.technician-map.heatmap-data') }}`)
                        ]);
                        
                        if (insightsRes.ok) {
                            this.insights = await insightsRes.json();
                            if (this.activeTab === 'cities') {
                                this.renderMapIcons();
                            }
                        }
                        
                        if (heatmapRes.ok) {
                            const hData = await heatmapRes.json();
                            this.heatmapDataPoints = hData.map(p => {
                                return {
                                    location: new google.maps.LatLng(p.lat, p.lng),
                                    weight: p.weight
                                };
                            });
                            this._updateHeatmapLayer();
                        }
                    } catch (error) {
                        console.error('[TechnicianMap] Insights Fetch failed:', error);
                    }
                },

                filterLists() {
                    const q = this.searchQuery.toLowerCase().trim();
                    if (!q) {
                        this.filteredTechnicians = [...this.technicians];
                    } else {
                        this.filteredTechnicians = this.technicians.filter(t =>
                            t.name.toLowerCase().includes(q) ||
                            t.phone.includes(q) ||
                            t.city.toLowerCase().includes(q)
                        );
                    }
                },
                
                toggleCityDetail(id) {
                    this.expandedCity = this.expandedCity === id ? null : id;
                },
                
                toggleHeatmap() {
                    this.heatmapVisible = !this.heatmapVisible;
                    this._updateHeatmapLayer();
                },
                
                toggleOrders() {
                    this.showOrders = !this.showOrders;
                    this.renderMapIcons();
                },
                
                _updateHeatmapLayer() {
                    if (this.heatmapVisible) {
                        if (!this.heatmapLayer) {
                            this.heatmapLayer = new google.maps.visualization.HeatmapLayer({
                                data: this.heatmapDataPoints,
                                map: this.map,
                                radius: 30,
                                opacity: 0.8
                            });
                        } else {
                            this.heatmapLayer.setData(this.heatmapDataPoints);
                            this.heatmapLayer.setMap(this.map);
                        }
                    } else if (this.heatmapLayer) {
                        this.heatmapLayer.setMap(null);
                    }
                },
                
                renderMapIcons() {
                    // Clear all markers
                    this._clearAllMarkers();
                    
                    if (this.activeTab === 'technicians') {
                        this.syncMarkers(this.technicians, true);
                        this.syncOrderMarkers(this.pendingOrders);
                    } else if (this.activeTab === 'cities' || this.activeTab === 'alerts') {
                        this.syncCityMarkers(this.insights.cities_overview);
                    }
                },

                _clearAllMarkers() {
                    // Remove technician markers from map
                    Object.values(this.markers).forEach(entry => {
                        entry.marker.setMap(null);
                        google.maps.event.clearInstanceListeners(entry.marker);
                    });
                    this.markers = {};
                    // Destroy clusterer
                    if (this.markerGroup) {
                        this.markerGroup.setMap(null);
                        this.markerGroup = null;
                    }
                    // Remove city markers
                    this.cityMarkers.forEach(cm => cm.setMap(null));
                    this.cityMarkers = [];
                    // Remove order markers
                    Object.values(this.orderMarkers).forEach(m => m.marker.setMap(null));
                    this.orderMarkers = {};
                },

                _clusterRenderer() {
                    return {
                        render: ({ count, position }) => {
                            let size = 40, bg = '#6366f1', fontSize = 13;
                            if (count >= 50) { size = 60; bg = '#ec4899'; fontSize = 16; }
                            else if (count >= 10) { size = 50; bg = '#8b5cf6'; fontSize = 14; }

                            return new google.maps.Marker({
                                position,
                                icon: {
                                    url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                                        <svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
                                            <circle cx="${size/2}" cy="${size/2}" r="${size/2-2}" fill="${bg}" stroke="rgba(255,255,255,0.8)" stroke-width="3"/>
                                            <text x="50%" y="52%" text-anchor="middle" dy=".35em" fill="white" font-family="Arial,sans-serif" font-weight="700" font-size="${fontSize}">${count}</text>
                                        </svg>
                                    `)}`,
                                    scaledSize: new google.maps.Size(size, size),
                                    anchor: new google.maps.Point(size/2, size/2),
                                },
                                zIndex: 1000 + count,
                            });
                        }
                    };
                },

                syncMarkers(technicians, forceRebuild = false) {
                    if (this.activeTab !== 'technicians') return;

                    if (forceRebuild) {
                        // We DO NOT destroy the clusterer. We will reuse it so it can clean up its own circles!

                        // 2. مسح الـ Markers الفردية من الخريطة ومن الـ Array
                        Object.values(this.markers).forEach(entry => {
                            entry.marker.setMap(null); // دي أهم خطوة عشان يختفوا من الخريطة فوراً
                            google.maps.event.clearInstanceListeners(entry.marker);
                        });
                        this.markers = {};

                        const newMarkers = [];
                        technicians.forEach(tech => {
                            if (!tech.latitude || !tech.longitude) return;
                            const statusColor = this._statusColors[tech.status] || '#9ca3af';
                            const pos = new google.maps.LatLng(parseFloat(tech.latitude), parseFloat(tech.longitude));

                            const marker = new google.maps.Marker({
                                position: pos,
                                title: tech.name,
                                icon: this._buildIcon(this._markerColor, statusColor),
                                zIndex: tech.status === 'online_available' ? 100 : (tech.status === 'online_busy' ? 50 : 10),
                            });
                            marker.addListener('click', () => {
                                const t = this.markers[tech.id]?.tech || tech;
                                this.infoWindow.setContent(this._popupHTML(t));
                                this.infoWindow.open({ anchor: marker, map: this.map });
                            });
                            this.markers[tech.id] = { marker, status: tech.status, tech };
                            newMarkers.push(marker);
                        });

                        if (this.markerGroup) {
                            // 3. Clear existing markers safely (pass true to avoid sync rendering bug)
                            //    and then add the new filtered markers allowing a clean render layout.
                            this.markerGroup.clearMarkers(true);
                            this.markerGroup.addMarkers(newMarkers);
                        } else {
                            this.markerGroup = new markerClusterer.MarkerClusterer({
                                map: this.map,
                                markers: newMarkers,
                                renderer: this._clusterRenderer(),
                            });
                        }

                    } else {
                        // ══ INCREMENTAL UPDATE (poll refresh — keeps clusterer alive) ══
                        // Safety: if clusterer doesn't exist yet (initial load was slow), do a full rebuild
                        if (!this.markerGroup) {
                            console.log('[MAP] No clusterer yet — escalating to full rebuild');
                            return this.syncMarkers(technicians, true);
                        }
                        const incomingIds = new Set();
                        const newMarkers = [];

                        technicians.forEach(tech => {
                            if (!tech.latitude || !tech.longitude) return;
                            incomingIds.add(tech.id);
                            const statusColor = this._statusColors[tech.status] || '#9ca3af';
                            const pos = new google.maps.LatLng(parseFloat(tech.latitude), parseFloat(tech.longitude));

                            if (this.markers[tech.id]) {
                                const entry = this.markers[tech.id];
                                entry.marker.setPosition(pos);
                                if (entry.status !== tech.status) {
                                    entry.marker.setIcon(this._buildIcon(this._markerColor, statusColor));
                                    entry.status = tech.status;
                                }
                                entry.tech = tech;
                            } else {
                                const marker = new google.maps.Marker({
                                    position: pos,
                                    title: tech.name,
                                    icon: this._buildIcon(this._markerColor, statusColor),
                                    zIndex: tech.status === 'online_available' ? 100 : (tech.status === 'online_busy' ? 50 : 10),
                                });
                                marker.addListener('click', () => {
                                    const t = this.markers[tech.id]?.tech || tech;
                                    this.infoWindow.setContent(this._popupHTML(t));
                                    this.infoWindow.open({ anchor: marker, map: this.map });
                                });
                                this.markers[tech.id] = { marker, status: tech.status, tech };
                                newMarkers.push(marker);
                            }
                        });

                        // Remove stale markers
                        const staleMarkers = [];
                        Object.keys(this.markers).forEach(id => {
                            if (!incomingIds.has(parseInt(id))) {
                                this.markers[id].marker.setMap(null);
                                google.maps.event.clearInstanceListeners(this.markers[id].marker);
                                staleMarkers.push(this.markers[id].marker);
                                delete this.markers[id];
                            }
                        });

                        if (this.markerGroup) {
                            if (staleMarkers.length) this.markerGroup.removeMarkers(staleMarkers);
                            if (newMarkers.length) this.markerGroup.addMarkers(newMarkers);
                        }
                    }
                },
                
                syncOrderMarkers(orders) {
                    if (this.activeTab !== 'technicians' || !this.showOrders) {
                        return;
                    }
                    
                    const incomingIds = new Set();
                    orders.forEach(order => {
                        if (!order.latitude || !order.longitude) return;
                        incomingIds.add(order.id);

                        const pos = new google.maps.LatLng(parseFloat(order.latitude), parseFloat(order.longitude));
                        
                        if (this.orderMarkers[order.id]) {
                            const entry = this.orderMarkers[order.id];
                            entry.marker.setPosition(pos);
                            entry.order = order;
                        } else {
                            const marker = new google.maps.Marker({
                                position: pos,
                                map: this.map,
                                title: order.customer_name,
                                icon: {
                                    url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" fill="#ec4899" stroke="#fff" stroke-width="2" filter="drop-shadow(0 2px 4px rgba(0,0,0,0.3))"/>
                                            <path fill="white" d="M12 6a3 3 0 0 0-3 3v1h-1v7h8v-7h-1v-1a3 3 0 0 0-3-3m0 2a1 1 0 0 1 1 1v1h-2v-1a1 1 0 0 1 1-1Z"/>
                                        </svg>
                                    `)}`,
                                    scaledSize: new google.maps.Size(30, 30),
                                    anchor: new google.maps.Point(15, 15),
                                },
                                animation: google.maps.Animation.DROP,
                                zIndex: 90,
                            });

                            marker.addListener('click', () => {
                                const o = this.orderMarkers[order.id]?.order || order;
                                this.infoWindow.setContent(this._popupOrderHTML(o));
                                this.infoWindow.open({ anchor: marker, map: this.map });
                            });

                            this.orderMarkers[order.id] = { marker, order };
                        }
                    });

                    Object.keys(this.orderMarkers).forEach(id => {
                        if (!incomingIds.has(parseInt(id))) {
                            this.orderMarkers[id].marker.setMap(null);
                            delete this.orderMarkers[id];
                        }
                    });
                },

                syncCityMarkers(cities) {
                    cities.forEach(city => {
                        if (!city.latitude || !city.longitude) return;
                        
                        const pos = new google.maps.LatLng(parseFloat(city.latitude), parseFloat(city.longitude));
                        const bg = city.coverage_status === 'good' ? '#22c55e' : (city.coverage_status === 'warning' ? '#eab308' : '#ef4444');
                        
                        const marker = new google.maps.Marker({
                            position: pos,
                            map: this.map,
                            title: city.name,
                            icon: {
                                url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                                    <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                                        <circle cx="25" cy="25" r="23" fill="${bg}" stroke="rgba(255,255,255,0.9)" stroke-width="3"/>
                                        <text x="50%" y="30%" text-anchor="middle" dy=".35em" fill="white" font-family="Arial,sans-serif" font-weight="700" font-size="12">${city.name}</text>
                                        <text x="50%" y="60%" text-anchor="middle" dy=".35em" fill="white" font-family="Arial,sans-serif" font-weight="700" font-size="16">${city.coverage_percentage}%</text>
                                    </svg>
                                `)}`,
                                scaledSize: new google.maps.Size(50, 50),
                                anchor: new google.maps.Point(25, 25),
                            },
                        });
                        
                        marker.addListener('click', () => {
                            this.activeTab = 'cities';
                            this.expandedCity = city.id;
                            this.map.panTo(pos);
                            this.map.setZoom(10);
                            
                            // Scroll panel
                            setTimeout(() => {
                                const el = document.querySelector('.side-panel-list');
                                if(el) el.scrollTop = 0; // simplistic scroll
                            }, 100);
                        });
                        
                        this.cityMarkers.push(marker);
                    });
                },

                _buildIcon(catColor, statusColor) {
                    return {
                        url: this._markerSvg(catColor, statusColor),
                        scaledSize: new google.maps.Size(32, 42),
                        anchor: new google.maps.Point(16, 42),
                    };
                },

                _popupHTML(tech) {
                    const categoriesHtml = (tech.categories || []).map(c => `<span class="cat-badge">${c.name}</span>`).join('');
                    
                    return `
                        <div class="popup-card">
                            <div class="popup-header">
                                <img src="${tech.avatar}" class="popup-avatar" alt="${tech.name}" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22><circle cx=%2212%22 cy=%2212%22 r=%2212%22 fill=%22%23e5e7eb%22/><path fill=%22%239ca3af%22 d=%22M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4m0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4%22/></svg>'">
                                <div style="min-width:0">
                                    <div class="popup-name">${tech.name}</div>
                                    <span class="popup-badge ${tech.status}">${this.statusLabels[tech.status]}</span>
                                </div>
                            </div>
                            <div class="popup-body">
                                <div class="popup-row">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24c1.12.37 2.33.57 3.57.57c.55 0 1 .45 1 1V20c0 .55-.45 1-1 1c-9.39 0-17-7.61-17-17c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1c0 1.25.2 2.45.57 3.57c.11.35.03.74-.25 1.02z"/></svg>
                                    <span dir="ltr">${tech.phone}</span>
                                </div>
                                <div class="popup-row">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15 11V5l-3-3l-3 3v2H3v14h18V11zm-8 8H5v-2h2zm0-4H5v-2h2zm0-4H5V9h2zm6 8h-2v-2h2zm0-4h-2v-2h2zm0-4h-2V9h2zm0-4h-2V5h2zm6 12h-2v-2h2zm0-4h-2v-2h2z"/></svg>
                                    <span>${tech.city} · ${tech.entity_type}</span>
                                </div>
                                <div class="popup-row">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2M12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8s-3.58 8-8 8m.5-13H11v6l5.25 3.15l.75-1.23l-4.5-2.67z"/></svg>
                                    <span>${tech.last_seen_at}</span>
                                </div>
                                ${categoriesHtml ? `<div class="popup-categories">${categoriesHtml}</div>` : ''}
                            </div>
                            <div class="popup-actions">
                                <a href="tel:${tech.phone}" class="popup-btn popup-btn-call">
                                    <svg width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24c1.12.37 2.33.57 3.57.57c.55 0 1 .45 1 1V20c0 .55-.45 1-1 1c-9.39 0-17-7.61-17-17c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1c0 1.25.2 2.45.57 3.57c.11.35.03.74-.25 1.02z"/></svg>
                                    {{ __('ui.call') }}
                                </a>
                                <a href="https://wa.me/${tech.phone.replace(/[^0-9]/g, '')}" target="_blank" class="popup-btn popup-btn-whatsapp">
                                    <svg width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967c-.273-.099-.471-.148-.67.15c-.197.297-.767.966-.94 1.164c-.173.199-.347.223-.644.075c-.297-.15-1.255-.463-2.39-1.475c-.883-.788-1.48-1.761-1.653-2.059c-.173-.297-.018-.458.13-.606c.134-.133.298-.347.446-.52c.149-.174.198-.298.298-.497c.099-.198.05-.371-.025-.52c-.075-.149-.669-1.612-.916-2.207c-.242-.579-.487-.5-.669-.51c-.173-.008-.371-.01-.57-.01c-.198 0-.52.074-.792.372c-.272.297-1.04 1.016-1.04 2.479c0 1.462 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487c.709.306 1.262.489 1.694.625c.712.227 1.36.195 1.871.118c.571-.085 1.758-.719 2.006-1.413c.248-.694.248-1.289.173-1.413c-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214l-3.741.982l.998-3.648l-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884c2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                    {{ __('ui.whatsapp') }}
                                </a>
                            </div>
                        </div>
                    `;
                },
                
                _popupOrderHTML(order) {
                    return `
                        <div class="popup-card">
                            <div class="popup-header" style="background:#fdf2f8;">
                                <div style="min-width:0; padding:4px 0;">
                                    <div class="popup-name">${order.customer_name}</div>
                                    <span class="popup-badge" style="background:#ec4899;">${order.category_name}</span>
                                </div>
                            </div>
                            <div class="popup-body">
                                <div class="popup-row">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2M12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8s-3.58 8-8 8m.5-13H11v6l5.25 3.15l.75-1.23l-4.5-2.67z"/></svg>
                                    <span>${order.created_at}</span>
                                </div>
                                <div class="popup-row" style="font-size:10px; color:#9ca3af; margin-top:4px;">
                                    {{ __('ui.pending_order') ?? 'طلب معلق' }} #${order.id}
                                </div>
                            </div>
                            <div class="popup-actions" style="background:#fdf2f8; border-top:1px solid #fce7f3;">
                                <a href="/dashboard/orders" class="popup-btn" style="background:#fbcfe8; color:#be185d;">
                                    {{ __('ui.view_order') ?? 'عرض تفاصيل الطلب' }} # ${order.id}
                                </a>
                            </div>
                        </div>
                    `;
                },

                flyToTechnician(tech) {
                    if (!tech.latitude || !tech.longitude || !this.map) return;

                    this.map.panTo({ lat: parseFloat(tech.latitude), lng: parseFloat(tech.longitude) });
                    this.map.setZoom(14);

                    setTimeout(() => {
                        const entry = this.markers[tech.id];
                        if (entry && entry.marker) {
                            google.maps.event.trigger(entry.marker, 'click');
                        }
                    }, 500);
                },

                resetView() {
                    if (this.map) {
                        this.infoWindow.close();
                        this.map.panTo({ lat: 24.0, lng: 45.0 });
                        this.map.setZoom(6);
                    }
                },

                toggleFullscreen() {
                    this.isFullscreen = !this.isFullscreen;
                    this.$nextTick(() => { if (this.map) google.maps.event.trigger(this.map, "resize"); });
                },

                destroy() {
                    if (this.pollInterval) clearInterval(this.pollInterval);
                }
            };
        }
    </script>

</x-layouts.dashboard>
