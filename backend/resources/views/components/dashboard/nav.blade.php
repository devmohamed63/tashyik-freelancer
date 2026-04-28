<div>
    <nav x-data="{ selected: $persist('overview') }">

        <!-- Main Group -->
        <div>

            @canany(['view dashboard', 'manage settings', 'view pages', 'create pages', 'view articles', 'create articles'])

                <x-dashboard.nav.label name="main" />

                <ul class="mb-6 flex flex-col gap-4">

                    @can('view dashboard')
                        <!-- Overview -->
                        <x-dashboard.nav.item
                            name="overview"
                            :url="route('dashboard.overview')">
                            <!-- ic:round-space-dashboard -->
                            <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
                            <path fill="currentColor" d="M9 21H5c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2m6 0h4c1.1 0 2-.9 2-2v-5c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v5c0 1.1.9 2 2 2m6-13V5c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h4c1.1 0 2-.9 2-2" />
                        </x-dashboard.nav.item>
                        <!-- Overview -->

                        <!-- Analytics -->
                        <x-dashboard.nav.item
                            name="analytics"
                            :url="route('dashboard.analytics')">
                            <!-- mdi:google-analytics -->
                            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
                            <path fill="currentColor" d="M15.86 4.39v15c0 1.67 1.14 2.61 2.39 2.61c1.14 0 2.39-.79 2.39-2.61V4.5c0-1.54-1.14-2.5-2.39-2.5s-2.39 1.06-2.39 2.39M9.61 12v7.39C9.61 21.07 10.77 22 12 22c1.14 0 2.39-.79 2.39-2.61v-7.28c0-1.54-1.14-2.5-2.39-2.5S9.61 10.67 9.61 12m-3.86 5.23c1.32 0 2.39 1.07 2.39 2.38a2.39 2.39 0 1 1-4.78 0c0-1.31 1.07-2.38 2.39-2.38" />
                        </x-dashboard.nav.item>
                        <!-- Analytics -->

                        <!-- Technician Map -->
                        <x-dashboard.nav.item
                            name="technician_map"
                            :url="route('dashboard.technician-map')">
                            <!-- material-symbols:map -->
                            <path fill="currentColor" d="M14.5 7a3.5 3.5 0 1 0-3.5 3.5A3.5 3.5 0 0 0 14.5 7M11 2a5 5 0 0 1 5 5c0 3.87-5 9-5 9S6 10.87 6 7a5 5 0 0 1 5-5m7.5 7A2.5 2.5 0 1 0 16 11.5A2.5 2.5 0 0 0 18.5 9M16 6a4 4 0 0 1 4 4c0 2.8-4 7-4 7s-1.42-1.49-2.53-3.21A7 7 0 0 0 16 6M3.5 9A2.5 2.5 0 1 1 6 11.5A2.5 2.5 0 0 1 3.5 9M6 6a4 4 0 0 0-4 4c0 2.8 4 7 4 7s1.42-1.49 2.53-3.21A7 7 0 0 1 6 6"/>
                        </x-dashboard.nav.item>
                        <!-- Technician Map -->
                    @endcan

                    @can('view financial reports')
                        <!-- Financial Reports -->
                        <x-dashboard.nav.item
                            name="financial_reports"
                            :url="route('dashboard.financial-reports')">
                            <!-- mdi:chart-areaspline -->
                            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
                            <path fill="currentColor" d="M17.45 15.18L22 7.31V21H2V3h2v14.54L9.5 8L16 12l5.61-9.75l1.74 1l-6.69 11.6l-6.53-3.84z" />
                        </x-dashboard.nav.item>
                        <!-- Financial Reports -->
                    @endcan

                    @can('manage reviews')
                        <!-- Reviews -->
                        <x-dashboard.nav.item
                            name="reviews"
                            :url="route('dashboard.reviews')">
                            <!-- mdi:star -->
                            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
                            <path fill="currentColor" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21z" />
                        </x-dashboard.nav.item>
                        <!-- Reviews -->
                    @endcan

                    @can('manage settings')
                        <!-- Settings -->
                        <x-dashboard.nav.item-group
                            name="settings"
                            :children="[['general_settings', route('dashboard.settings.index', ['tab' => 'basic-information']), true], ['about-us', route('dashboard.settings.edit_default_page', ['page' => 'about-us']), true], ['terms-and-conditions', route('dashboard.settings.edit_default_page', ['page' => 'terms-and-conditions']), true], ['privacy-policy', route('dashboard.settings.edit_default_page', ['page' => 'privacy-policy']), true]]"
                            icon-viewBox="0 0 36 36">
                            <!-- clarity:settings-solid -->
                            <!-- Icon from Clarity by VMware - https://github.com/vmware/clarity-assets/blob/master/LICENSE -->
                            <path fill="currentColor"
                                d="m32.57 15.72l-3.35-1a11.7 11.7 0 0 0-.95-2.33l1.64-3.07a.61.61 0 0 0-.11-.72l-2.39-2.4a.61.61 0 0 0-.72-.11l-3.05 1.63a11.6 11.6 0 0 0-2.36-1l-1-3.31a.61.61 0 0 0-.59-.41h-3.38a.61.61 0 0 0-.58.43l-1 3.3a11.6 11.6 0 0 0-2.38 1l-3-1.62a.61.61 0 0 0-.72.11L6.2 8.59a.61.61 0 0 0-.11.72l1.62 3a11.6 11.6 0 0 0-1 2.37l-3.31 1a.61.61 0 0 0-.43.58v3.38a.61.61 0 0 0 .43.58l3.33 1a11.6 11.6 0 0 0 1 2.33l-1.64 3.14a.61.61 0 0 0 .11.72l2.39 2.39a.61.61 0 0 0 .72.11l3.09-1.65a11.7 11.7 0 0 0 2.3.94l1 3.37a.61.61 0 0 0 .58.43h3.38a.61.61 0 0 0 .58-.43l1-3.38a11.6 11.6 0 0 0 2.28-.94l3.11 1.66a.61.61 0 0 0 .72-.11l2.39-2.39a.61.61 0 0 0 .11-.72l-1.66-3.1a11.6 11.6 0 0 0 .95-2.29l3.37-1a.61.61 0 0 0 .43-.58v-3.41a.61.61 0 0 0-.37-.59M18 23.5a5.5 5.5 0 1 1 5.5-5.5a5.5 5.5 0 0 1-5.5 5.5"
                                class="clr-i-solid clr-i-solid-path-1" />
                            <path fill="none" d="M0 0h36v36H0z" />
                        </x-dashboard.nav.item-group>
                        <!-- Settings -->
                    @endcan

                    {{-- @canany(['viewAny', 'create'], App\Models\Page::class)
                        <!-- Pages -->
                        <x-dashboard.nav.item-group
                            name="pages"
                            :children="[['add_pages', route('dashboard.pages.create'), Gate::allows('create', App\Models\Page::class)], ['view_pages', route('dashboard.pages.index'), Gate::allows('viewAny', App\Models\Page::class)]]">
                            <!-- mingcute:document-2-fill -->
                            <!-- Icon from MingCute Icon by MingCute Design - https://github.com/Richard9394/MingCute/blob/main/LICENSE -->
                            <g fill="none" fill-rule="evenodd">
                                <path d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                <path fill="currentColor" d="M12 2v6.5a1.5 1.5 0 0 0 1.5 1.5H20v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm3 13H9a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2m-5-4H9a1 1 0 1 0 0 2h1a1 1 0 1 0 0-2m4-8.957a2 2 0 0 1 1 .543L19.414 7a2 2 0 0 1 .543 1H14Z" />
                            </g>
                        </x-dashboard.nav.item-group>
                        <!-- Pages -->
                    @endcanany --}}

                    @canany(['viewAny', 'create'], App\Models\Article::class)
                        <!-- Articles -->
                        <x-dashboard.nav.item-group
                            name="articles"
                            :badge="\App\Models\Article::count()"
                            :children="[['add_articles', route('dashboard.articles.create'), Gate::allows('create', App\Models\Article::class)], ['view_articles', route('dashboard.articles.index'), Gate::allows('viewAny', App\Models\Article::class)]]">
                            <!-- mdi:newspaper-variant-outline -->
                            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
                            <path fill="currentColor" d="M20 5L20 19L4 19L4 5H20M20 3H4C2.89 3 2 3.89 2 5V19C2 20.11 2.89 21 4 21H20C21.11 21 22 20.11 22 19V5C22 3.89 21.11 3 20 3M18 15H6V17H18V15M10 7H6V13H10V7M18 7H12V9H18V7M18 11H12V13H18V11Z" />
                        </x-dashboard.nav.item-group>
                        <!-- Articles -->
                    @endcanany

                </ul>

            @endcanany

            @can('viewAny', App\Models\Banner::class)
                <x-dashboard.nav.label name="nav_push_notifications" />

                <ul class="mb-6 flex flex-col gap-4">
                    <x-dashboard.nav.item-group
                        name="push_ads"
                        :children="[
                            ['view_push_ads', route('dashboard.push-ads.index'), true],
                            ['create_push_ad', route('dashboard.push-ads.create'), Gate::allows('create', App\Models\Banner::class)],
                        ]">
                        <!-- mdi:send (push notifications) -->
                        <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
                        <path fill="currentColor" d="M2,21L23,12L2,3V10L17,12L2,14V21Z" />
                    </x-dashboard.nav.item-group>
                </ul>

                <x-dashboard.nav.label name="nav_slider_banners" />

                <ul class="mb-6 flex flex-col gap-4">
                    <x-dashboard.nav.item-group
                        name="slider_banners"
                        :children="[
                            ['view_banners', route('dashboard.banners.index'), true],
                            ['create_banner', route('dashboard.banners.create'), Gate::allows('create', App\Models\Banner::class)],
                            ['show_banner', '', false],
                            ['edit_banner', '', false],
                        ]">
                        <!-- bxs:image -->
                        <!-- Icon from BoxIcons Solid by Atisa - https://creativecommons.org/licenses/by/4.0/ -->
                        <path fill="currentColor" d="M19.999 4h-16c-1.103 0-2 .897-2 2v12c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2V6c0-1.103-.897-2-2-2m-13.5 3a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3m5.5 10h-7l4-5l1.5 2l3-4l5.5 7z" />
                    </x-dashboard.nav.item-group>
                </ul>
            @endcan

            @canany(['view cities', 'create cities'])

                <x-dashboard.nav.label name="cities_management" />

                <ul class="mb-6 flex flex-col gap-4">
                    @canany(['viewAny', 'create'], App\Models\City::class)
                        <!-- Cities -->
                        <x-dashboard.nav.item
                            name="cities"
                            :url="route('dashboard.cities.index')"
                            :badge="\App\Models\City::count()">
                            <!-- material-symbols:location-on-rounded -->
                            <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
                            <path fill="currentColor" d="M12 21.325q-.35 0-.7-.125t-.625-.375Q9.05 19.325 7.8 17.9t-2.087-2.762t-1.275-2.575T4 10.2q0-3.75 2.413-5.975T12 2t5.588 2.225T20 10.2q0 1.125-.437 2.363t-1.275 2.575T16.2 17.9t-2.875 2.925q-.275.25-.625.375t-.7.125M12 12q.825 0 1.413-.587T14 10t-.587-1.412T12 8t-1.412.588T10 10t.588 1.413T12 12" />
                        </x-dashboard.nav.item>
                        <!-- Cities -->
                    @endcan
                </ul>

            @endcanany

            @canany(['view categories', 'create categories', 'view services', 'create services', 'manage orders', 'manage coupons', 'manage promotions'])

                <x-dashboard.nav.label name="services_management" />

                <ul class="mb-6 flex flex-col gap-4">

                    @canany(['viewAny', 'create'], App\Models\Category::class)
                        <!-- Categories -->
                        <x-dashboard.nav.item-group
                            name="categories"
                            :badge="\App\Models\Category::isParent()->count()"
                            :children="[
                                ['add_categories', route('dashboard.categories.create'), Gate::allows('create', App\Models\Category::class)],
                                ['view_categories', route('dashboard.categories.index'), Gate::allows('viewAny', App\Models\Category::class)],
                                ['view_subcategories', route('dashboard.categories.children'), Gate::allows('viewAny', App\Models\Category::class), \App\Models\Category::isChild()->count()]
                            ]">
                            <!-- mdi:tag -->
                            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
                            <path fill="currentColor" d="M5.5 7A1.5 1.5 0 0 1 4 5.5A1.5 1.5 0 0 1 5.5 4A1.5 1.5 0 0 1 7 5.5A1.5 1.5 0 0 1 5.5 7m15.91 4.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.11 0-2 .89-2 2v7c0 .55.22 1.05.59 1.41l8.99 9c.37.36.87.59 1.42.59s1.05-.23 1.41-.59l7-7c.37-.36.59-.86.59-1.41c0-.56-.23-1.06-.59-1.42" />
                        </x-dashboard.nav.item-group>
                        <!-- Categories -->
                    @endcanany

                    @canany(['viewAny', 'create'], App\Models\Service::class)
                        <!-- Services -->
                        <x-dashboard.nav.item-group
                            name="services"
                            :badge="\App\Models\Service::count()"
                            :children="[
                                ['add_services', route('dashboard.services.create'), Gate::allows('create', App\Models\Service::class)], 
                                ['view_services', route('dashboard.services.index'), Gate::allows('viewAny', App\Models\Service::class)]
                            ]">
                            <!-- heroicons:wrench-screwdriver-solid -->
                            <!-- Icon from HeroIcons by Refactoring UI Inc - https://github.com/tailwindlabs/heroicons/blob/master/LICENSE -->
                            <g fill="currentColor">
                                <path fill-rule="evenodd" d="M12 6.75a5.25 5.25 0 0 1 6.775-5.025a.75.75 0 0 1 .313 1.248l-3.32 3.319a2.25 2.25 0 0 0 1.941 1.939l3.318-3.319a.75.75 0 0 1 1.248.313a5.25 5.25 0 0 1-5.472 6.756c-1.018-.086-1.87.1-2.309.634L7.344 21.3A3.298 3.298 0 1 1 2.7 16.657l8.684-7.151c.533-.44.72-1.291.634-2.309A5 5 0 0 1 12 6.75M4.117 19.125a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75h-.008a.75.75 0 0 1-.75-.75z" clip-rule="evenodd" />
                                <path d="m10.076 8.64l-2.201-2.2V4.874a.75.75 0 0 0-.364-.643l-3.75-2.25a.75.75 0 0 0-.916.113l-.75.75a.75.75 0 0 0-.113.916l2.25 3.75a.75.75 0 0 0 .643.364h1.564l2.062 2.062z" />
                                <path fill-rule="evenodd" d="m12.556 17.329l4.183 4.182a3.375 3.375 0 0 0 4.773-4.773l-3.306-3.305a6.8 6.8 0 0 1-1.53.043c-.394-.034-.682-.006-.867.042a.6.6 0 0 0-.167.063zm3.414-1.36a.75.75 0 0 1 1.06 0l1.875 1.876a.75.75 0 1 1-1.06 1.06L15.97 17.03a.75.75 0 0 1 0-1.06" clip-rule="evenodd" />
                            </g>
                        </x-dashboard.nav.item-group>
                        <!-- Services -->
                    @endcanany

                    @can('manage orders')
                        <!-- Orders -->
                        <x-dashboard.nav.item
                            name="orders"
                            :url="route('dashboard.orders.index')"
                            :badge="\App\Models\Order::isNew()->count()">
                            <!-- ic:sharp-home-repair-service -->
                            <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
                            <path fill="currentColor" d="M18 16h-2v-1H8v1H6v-1H2v5h20v-5h-4zm-1-8V4H7v4H2v6h4v-2h2v2h8v-2h2v2h4V8zM9 6h6v2H9z" />
                        </x-dashboard.nav.item>
                        <!-- Orders -->
                    @endcan

                    @can('manage coupons')
                        <!-- Coupons -->
                        <x-dashboard.nav.item
                            name="coupons"
                            :url="route('dashboard.coupons.index')">
                            <!-- heroicons:ticket-solid -->
                            <!-- Icon from HeroIcons by Refactoring UI Inc - https://github.com/tailwindlabs/heroicons/blob/master/LICENSE -->
                            <path fill="currentColor" fill-rule="evenodd" d="M1.5 6.375c0-1.036.84-1.875 1.875-1.875h17.25c1.035 0 1.875.84 1.875 1.875v3.026a.75.75 0 0 1-.375.65a2.249 2.249 0 0 0 0 3.898a.75.75 0 0 1 .375.65v3.026c0 1.035-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 17.625v-3.026a.75.75 0 0 1 .374-.65a2.249 2.249 0 0 0 0-3.898a.75.75 0 0 1-.374-.65zm15-1.125a.75.75 0 0 1 .75.75v.75a.75.75 0 0 1-1.5 0V6a.75.75 0 0 1 .75-.75m.75 4.5a.75.75 0 0 0-1.5 0v.75a.75.75 0 0 0 1.5 0zm-.75 3a.75.75 0 0 1 .75.75v.75a.75.75 0 0 1-1.5 0v-.75a.75.75 0 0 1 .75-.75m.75 4.5a.75.75 0 0 0-1.5 0V18a.75.75 0 0 0 1.5 0zM6 12a.75.75 0 0 1 .75-.75H12a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 12m.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5z" clip-rule="evenodd" />
                        </x-dashboard.nav.item>
                        <!-- Coupons -->
                    @endcan

                    @can('manage promotions')
                        <!-- Promotions -->
                        <x-dashboard.nav.item
                            name="promotions"
                            :url="route('dashboard.promotions.index')">
                            <!-- ri:discount-percent-fill -->
                            <!-- Icon from Remix Icon by Remix Design - https://github.com/Remix-Design/RemixIcon/blob/master/License -->
                            <path fill="currentColor" d="M13.946 2.094a3 3 0 0 0-3.892 0L8.706 3.243a1 1 0 0 1-.569.236l-1.765.14A3 3 0 0 0 3.62 6.371l-.14 1.766a1 1 0 0 1-.237.569l-1.148 1.348a3 3 0 0 0 0 3.891l1.148 1.349a1 1 0 0 1 .236.569l.141 1.765a3 3 0 0 0 2.752 2.752l1.765.14a1 1 0 0 1 .57.237l1.347 1.148a3 3 0 0 0 3.892 0l1.348-1.148a1 1 0 0 1 .57-.236l1.765-.141a3 3 0 0 0 2.752-2.752l.14-1.765a1 1 0 0 1 .236-.57l1.149-1.347a3 3 0 0 0 0-3.892l-1.149-1.348a1 1 0 0 1-.236-.57l-.14-1.765a3 3 0 0 0-2.752-2.752l-1.766-.14a1 1 0 0 1-.569-.236zm.882 5.663l1.415 1.414l-7.071 7.072l-1.415-1.415zm-4.596 2.475a1.5 1.5 0 1 1-2.121-2.121a1.5 1.5 0 0 1 2.121 2.121m3.536 5.657a1.5 1.5 0 1 1 2.12-2.121a1.5 1.5 0 0 1-2.12 2.12" />
                        </x-dashboard.nav.item>
                        <!-- Promotions -->
                    @endcan
                </ul>

            @endcanany

            @canany(['view users', 'create users', 'manage plans', 'manage subscriptions'])

                <x-dashboard.nav.label name="users_management" />

                <ul class="mb-6 flex flex-col gap-4">

                    @canany(['viewAny', 'create'], App\Models\User::class)
                        <!-- Users -->
                        <x-dashboard.nav.item-group
                            name="users"
                            :children="[
                                ['add_users', route('dashboard.users.create'), Gate::allows('create', App\Models\User::class)],
                                ['view_users', route('dashboard.users.index'), Gate::allows('viewAny', App\Models\User::class)],
                                ['view_service_providers', route('dashboard.users.service_providers'), Gate::allows('viewAny', App\Models\User::class)],
                                ['view_institutions', route('dashboard.users.service_providers', ['typeFilter' => 'institution']), Gate::allows('viewAny', App\Models\User::class)],
                                ['view_companies', route('dashboard.users.service_providers', ['typeFilter' => 'company']), Gate::allows('viewAny', App\Models\User::class)],
                                ['add_service_provider', route('dashboard.users.create_service_provider'), Gate::allows('create', App\Models\User::class)],
                                ['view_payout_requests', route('dashboard.users.payout_requests'), Gate::allows('viewAny', App\Models\User::class)],
                            ]">
                            <!-- mage:users-fill -->
                            <!-- Icon from Mage Icons by MageIcons - https://github.com/Mage-Icons/mage-icons/blob/main/License.txt -->
                            <path fill="currentColor" d="M21.987 18.73a2 2 0 0 1-.34.85a1.9 1.9 0 0 1-1.56.8h-1.651a.74.74 0 0 1-.6-.31a.76.76 0 0 1-.11-.67c.37-1.18.29-2.51-3.061-4.64a.77.77 0 0 1-.32-.85a.76.76 0 0 1 .72-.54a7.61 7.61 0 0 1 6.792 4.39a2 2 0 0 1 .13.97M19.486 7.7a4.43 4.43 0 0 1-4.421 4.42a.76.76 0 0 1-.65-1.13a6.16 6.16 0 0 0 0-6.53a.75.75 0 0 1 .61-1.18a4.3 4.3 0 0 1 3.13 1.34a4.46 4.46 0 0 1 1.291 3.12z" />
                            <path fill="currentColor" d="M16.675 18.7a2.65 2.65 0 0 1-1.26 2.48c-.418.257-.9.392-1.39.39H4.652a2.63 2.63 0 0 1-1.39-.39A2.62 2.62 0 0 1 2.01 18.7a2.6 2.6 0 0 1 .5-1.35a8.8 8.8 0 0 1 6.812-3.51a8.78 8.78 0 0 1 6.842 3.5a2.7 2.7 0 0 1 .51 1.36M14.245 7.32a4.92 4.92 0 0 1-4.902 4.91a4.903 4.903 0 0 1-4.797-5.858a4.9 4.9 0 0 1 6.678-3.57a4.9 4.9 0 0 1 3.03 4.518z" />
                        </x-dashboard.nav.item-group>
                        <!-- Users -->
                    @endcanany

                    @can('manage plans')
                        <!-- Plans -->
                        <x-dashboard.nav.item
                            name="plans"
                            :url="route('dashboard.plans.index')">
                            <!-- mynaui:rows-solid -->
                            <!-- Icon from Myna UI Icons by Praveen Juge - https://github.com/praveenjuge/mynaui-icons/blob/main/LICENSE -->
                            <path fill="currentColor" d="M4 4.75A1.75 1.75 0 0 0 2.25 6.5v3c0 .966.784 1.75 1.75 1.75h16a1.75 1.75 0 0 0 1.75-1.75v-3A1.75 1.75 0 0 0 20 4.75zm0 8a1.75 1.75 0 0 0-1.75 1.75v3c0 .966.784 1.75 1.75 1.75h16a1.75 1.75 0 0 0 1.75-1.75v-3A1.75 1.75 0 0 0 20 12.75z" />
                        </x-dashboard.nav.item>
                        <!-- Plans -->
                    @endcan

                    @can('manage subscriptions')
                        <!-- Subscriptions -->
                        <x-dashboard.nav.item
                            name="subscriptions"
                            :url="route('dashboard.subscriptions.index')">
                            <!-- ri:money-dollar-circle-fill -->
                            <!-- Icon from Remix Icon by Remix Design - https://github.com/Remix-Design/RemixIcon/blob/master/License -->
                            <path fill="currentColor" d="M12.005 22.003c-5.523 0-10-4.477-10-10s4.477-10 10-10s10 4.477 10 10s-4.477 10-10 10m-3.5-8v2h2.5v2h2v-2h1a2.5 2.5 0 1 0 0-5h-4a.5.5 0 1 1 0-1h5.5v-2h-2.5v-2h-2v2h-1a2.5 2.5 0 1 0 0 5h4a.5.5 0 0 1 0 1z" />
                        </x-dashboard.nav.item>
                        <!-- Subscriptions -->
                    @endcan

                </ul>

            @endcanany

            @canany(['manage roles and permissions'])

                <x-dashboard.nav.label name="roles_management" />

                <ul class="mb-6 flex flex-col gap-4">

                    @can('manage roles and permissions')
                        <!-- Roles and Permissions -->
                        <x-dashboard.nav.item
                            name="roles_and_permissions"
                            :url="route('dashboard.roles.index')">
                            <!-- mdi:shield-account-variant -->
                            <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
                            <path fill="currentColor" d="M17 11c.3 0 .7 0 1 .1V6.3L10.5 3L3 6.3v4.9c0 4.5 3.2 8.8 7.5 9.8c.6-.1 1.1-.3 1.6-.5c-.7-1-1.1-2.2-1.1-3.5c0-3.3 2.7-6 6-6m0 2c-2.2 0-4 1.8-4 4s1.8 4 4 4s4-1.8 4-4s-1.8-4-4-4m0 1.4c.6 0 1.1.5 1.1 1.1s-.5 1.1-1.1 1.1s-1.1-.5-1.1-1.1s.5-1.1 1.1-1.1m0 5.4c-.9 0-1.7-.5-2.2-1.2c.1-.7 1.5-1.1 2.2-1.1s2.2.4 2.2 1.1c-.5.7-1.3 1.2-2.2 1.2" />
                        </x-dashboard.nav.item>
                        <!-- Roles and Permissions -->
                    @endcan

                </ul>

            @endcanany

            @canany(['manage contact requests'])

                <x-dashboard.nav.label name="contact_requests" />

                <ul class="mb-6 flex flex-col gap-4">

                    @can('manage contact requests')
                        <!-- Contact Requests -->
                        <x-dashboard.nav.item
                            name="contact_requests"
                            :url="route('dashboard.contacts.index')"
                            :badge="\App\Models\Contact::where('is_read', false)->count() ?: null"
                            viewBox="0 0 56 56">
                            <!-- f7:envelope-fill -->
                            <!-- Icon from Framework7 Icons by Vladimir Kharlampidi - https://github.com/framework7io/framework7-icons/blob/master/LICENSE -->
                            <path fill="currentColor" d="M28.047 30.707c.984 0 1.875-.445 2.883-1.477L51.32 9.05c-.867-.843-2.484-1.241-4.804-1.241H8.78c-1.969 0-3.351.375-4.125 1.148l20.508 20.274c1.008 1.007 1.922 1.476 2.883 1.476M2.71 44.418l16.57-16.383L2.664 11.652c-.352.657-.54 1.782-.54 3.399v25.875c0 1.664.212 2.836.587 3.492m50.625-.023c.351-.68.54-1.829.54-3.47V15.052c0-1.57-.165-2.696-.517-3.328L36.812 28.035ZM9.484 48.19h37.734c1.97 0 3.329-.375 4.102-1.125L34.445 30.332l-1.57 1.57c-1.594 1.547-3.117 2.25-4.828 2.25s-3.235-.703-4.828-2.25l-1.57-1.57L4.796 47.043c.89.773 2.46 1.148 4.687 1.148" />
                        </x-dashboard.nav.item>
                        <!-- Contact Requests -->
                    @endcan

                </ul>

            @endcanany

        </div>

    </nav>
</div>
