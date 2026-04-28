<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.show_payout_request')" />

        <div class="space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <!-- Phone -->
                <x-dashboard.info-label :name="__('validation.attributes.phone')" :value="$serviceProvider?->phone" />

                <!-- Bank account name -->
                <x-dashboard.info-label :name="__('validation.attributes.bank_name')" :value="$serviceProvider?->bank_name ?? __('ui.none')" />

                <!-- IBAN -->
                <x-dashboard.info-label :name="__('validation.attributes.iban')" :value="$serviceProvider?->iban ?? __('ui.none')" />

                <!-- Balance -->
                <x-dashboard.info-label :name="__('ui.balance')" :value="number_format($serviceProvider?->balance, config('app.decimal_places')) . ' ' . __('ui.currency')" />

                @if ($serviceProvider?->balance > 0)
                    <div class="md:mt-3">
                        <!-- Zero the balance -->
                        <x-dashboard.tables.buttons.wallet wire:click="zeroThebalance()" />
                    </div>
                @endif
            </div>

            <x-dashboard.ui.hr />

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('ui.id') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('validation.attributes.type') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('ui.action') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('ui.amount') }}</th>
                            <th scope="col" class="px-6 py-3">Daftra</th>
                            <th scope="col" class="px-6 py-3">{{ __('ui.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices ?? [] as $invoice)
                            <tr class="{{ $invoice?->action == App\Models\Invoice::CREDIT_ACTION ? 'bg-green-50 text-green-900 dark:bg-green-500/20 dark:text-green-50' : 'bg-yellow-50 text-yellow-900 dark:bg-yellow-500/20 dark:text-yellow-50' }}">
                                <th scope="row" class="font-normal px-6 py-4 whitespace-nowrap">
                                    {{ $invoice->target_id ?? '#' }}
                                </th>
                                <th class="px-6 py-4 font-medium whitespace-nowrap">
                                    {{ $invoice->translated_type }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ __('ui.' . $invoice?->action) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ number_format($invoice?->amount, config('app.decimal_places')) . ' ' . __('ui.currency') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($invoice->type === App\Models\Invoice::BANK_TRANSFER_TYPE)
                                        @if ($invoice->recorded_in_daftra)
                                            <span class="text-xs font-semibold">Recorded</span>
                                        @else
                                            <button
                                                type="button"
                                                class="text-xs font-semibold underline"
                                                wire:click="markBankTransferRecorded({{ $invoice->id }})"
                                            >
                                                Mark as recorded
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    {{ $invoice?->created_at?->isoFormat(config('app.time_format')) }}
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <td class="px-5 py-8 sm:px-6 text-center text-gray-500 dark:text-gray-400" colspan="999">
                                    {{ __('ui.no_results') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('ui.id') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('validation.attributes.type') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('ui.action') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('ui.amount') }}</th>
                            <th scope="col" class="px-6 py-3">Daftra</th>
                            <th scope="col" class="px-6 py-3">{{ __('ui.date') }}</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="p-2">
                    {{ $invoices?->onEachSide(1)->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
