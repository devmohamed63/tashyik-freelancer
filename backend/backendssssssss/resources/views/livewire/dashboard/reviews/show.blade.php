<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.review_details')" />

        @if ($review)
        <div class="space-y-5">

            {{-- Rating --}}
            <div class="flex items-center justify-center gap-2 py-3">
                <div class="flex items-center gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-7 h-7 {{ $i <= $starCount ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21z" />
                        </svg>
                    @endfor
                </div>
                <span class="text-lg font-bold text-gray-700 dark:text-gray-300 ms-2">{{ $starCount }}/5</span>
            </div>

            {{-- Comment --}}
            @if ($body)
            <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('ui.comment') }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $body }}</p>
            </div>
            @endif

            {{-- Customer Info --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('ui.customer') }}</p>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $customer?->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" dir="ltr">{{ $customerPhone ?? '-' }}</p>
                </div>
                @if ($customerWhatsApp)
                <a href="{{ $customerWhatsApp }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg text-white px-4 py-2.5 text-sm font-medium transition-all w-full justify-center"
                   style="background-color: #25D366;"
                   onmouseover="this.style.backgroundColor='#1da851'" onmouseout="this.style.backgroundColor='#25D366'">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967c-.273-.099-.471-.148-.67.15c-.197.297-.767.966-.94 1.164c-.173.199-.347.223-.644.075c-.297-.15-1.255-.463-2.39-1.475c-.883-.788-1.48-1.761-1.653-2.059c-.173-.297-.018-.458.13-.606c.134-.133.298-.347.446-.52c.149-.174.198-.298.298-.497c.099-.198.05-.371-.025-.52c-.075-.149-.669-1.612-.916-2.207c-.242-.579-.487-.5-.669-.51c-.173-.008-.371-.01-.57-.01c-.198 0-.52.074-.792.372c-.272.297-1.04 1.016-1.04 2.479c0 1.462 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487c.709.306 1.262.489 1.694.625c.712.227 1.36.195 1.871.118c.571-.085 1.758-.719 2.006-1.413c.248-.694.248-1.289.173-1.413c-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214l-3.741.982l.998-3.648l-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884c2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/>
                    </svg>
                    {{ __('ui.contact_via_whatsapp') }}
                </a>
                @endif
            </div>

            {{-- Service Provider Info --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('ui.service_provider') }}</p>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ $serviceProvider?->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" dir="ltr">{{ $providerPhone ?? '-' }}</p>
                </div>
                @if ($providerWhatsApp)
                <a href="{{ $providerWhatsApp }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg text-white px-4 py-2.5 text-sm font-medium transition-all w-full justify-center"
                   style="background-color: #25D366;"
                   onmouseover="this.style.backgroundColor='#1da851'" onmouseout="this.style.backgroundColor='#25D366'">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967c-.273-.099-.471-.148-.67.15c-.197.297-.767.966-.94 1.164c-.173.199-.347.223-.644.075c-.297-.15-1.255-.463-2.39-1.475c-.883-.788-1.48-1.761-1.653-2.059c-.173-.297-.018-.458.13-.606c.134-.133.298-.347.446-.52c.149-.174.198-.298.298-.497c.099-.198.05-.371-.025-.52c-.075-.149-.669-1.612-.916-2.207c-.242-.579-.487-.5-.669-.51c-.173-.008-.371-.01-.57-.01c-.198 0-.52.074-.792.372c-.272.297-1.04 1.016-1.04 2.479c0 1.462 1.065 2.875 1.213 3.074c.149.198 2.096 3.2 5.077 4.487c.709.306 1.262.489 1.694.625c.712.227 1.36.195 1.871.118c.571-.085 1.758-.719 2.006-1.413c.248-.694.248-1.289.173-1.413c-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214l-3.741.982l.998-3.648l-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884c2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/>
                    </svg>
                    {{ __('ui.contact_via_whatsapp') }}
                </a>
                @endif
            </div>

            {{-- Date --}}
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $createdAt }}</span>
            </div>

        </div>
        @endif

    </div>
</div>
