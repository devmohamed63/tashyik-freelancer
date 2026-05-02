<div>
    <div class="space-y-5">
        {{-- Modal loader --}}
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" wire:target="file,import" />

        {{-- Title --}}
        <x-dashboard.modals.title :value="__('ui.import_customers')" />

        @if ($error)
            <x-dashboard.alerts.error :title="$error" />
        @endif

        @if ($result)
            {{-- Result summary --}}
            <x-dashboard.alerts.success :title="__('ui.import_done')" />

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-white/[0.03]">
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ number_format($result['imported']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.imported_count') }}</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-white/[0.03]">
                    <p class="text-2xl font-bold text-warning-600 dark:text-orange-400">{{ number_format($result['duplicates']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('ui.skipped_duplicates') }}
                        @if ($result['trashed'] > 0)
                            <span class="block text-[11px] text-gray-400 mt-0.5">
                                ({{ number_format($result['trashed']) }} {{ __('ui.trashed_duplicates') }})
                            </span>
                        @endif
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 text-center dark:border-gray-700 dark:bg-white/[0.03]">
                    <p class="text-2xl font-bold text-error-600 dark:text-error-400">{{ number_format(count($result['invalid'])) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('ui.invalid_rows') }}</p>
                </div>
            </div>

            @if (count($result['invalid']) > 0)
                <details class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-white/[0.03] p-4">
                    <summary class="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('ui.invalid_rows_label') }}
                    </summary>
                    <div class="mt-3 max-h-60 overflow-y-auto">
                        <table class="w-full text-sm text-start text-gray-600 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2">{{ __('ui.row_number') }}</th>
                                    <th class="px-3 py-2">{{ __('ui.original_value') }}</th>
                                    <th class="px-3 py-2">{{ __('ui.reason') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['invalid'] as $row)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="px-3 py-2">{{ $row['row'] }}</td>
                                        <td class="px-3 py-2 font-mono" dir="ltr">{{ $row['value'] }}</td>
                                        <td class="px-3 py-2">{{ $row['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif

            <div class="flex flex-wrap gap-3">
                <button type="button" wire:click="resetForm"
                    class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                    {{ __('ui.import_customers') }}
                </button>

                <button type="button" onclick="hideModal('importCustomersModal')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.05]">
                    {{ __('ui.no_cancel') }}
                </button>
            </div>
        @else
            {{-- Hint + template download --}}
            <x-dashboard.alerts.info :title="__('ui.excel_import_hint', ['max' => \App\Livewire\Dashboard\Users\ImportCustomers::MAX_ROWS])" />

            <a href="{{ route('dashboard.users.import_template') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                {{-- material-symbols:download --}}
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 16l-5-5h3V4h4v7h3zM5 20v-2h14v2z" /></svg>
                {{ __('ui.download_template') }}
            </a>

            <form class="flex flex-col gap-5" wire:submit="import" x-data="{ loading: false }" x-on:submit="loading = true">
                <x-dashboard.inputs.file.default
                    id="import-customers-file"
                    name="file"
                    input-name="file"
                    wire:model="file"
                    accept=".xlsx,.xls,.csv"
                    :required="true" />

                {{-- Upload progress --}}
                <div wire:loading wire:target="file" class="text-sm text-gray-500">
                    {{ __('ui.loading') }}
                </div>

                @if ($file)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ $file->getClientOriginalName() }}</span>
                    </div>
                @endif

                <div class="flex flex-wrap gap-3">
                    <x-dashboard.buttons.primary :name="__('ui.start_import')" />

                    <button type="button" onclick="hideModal('importCustomersModal')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.05]">
                        {{ __('ui.no_cancel') }}
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
