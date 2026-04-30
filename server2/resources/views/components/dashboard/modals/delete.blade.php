<div wire:ignore>
    <div class="hidden fixed inset-0 items-center justify-center p-5 overflow-y-auto modal z-99999" id="{{ $id }}">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[10px]"></div>
        <div class="modal-dialog modal-dialog-scrollable modal-lg no-scrollbar relative flex w-full max-w-[500px] flex-col overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 max-h-[calc(100dvh-3rem)]">
            <div class="flex flex-col px-2 overflow-y-auto modal-content custom-scrollbar">
                <div class="modal-body">
                    <!-- Modal loader -->
                    <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

                    <div class="p-4 md:p-5 text-center">
                        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{{ __('ui.delete_results_alert') }}</h3>
                        <div class="flex flex-row gap-5 items-center justify-center">
                            <x-dashboard.buttons.danger wire:click="confirmDelete" type="button" :name="__('ui.yes_sure')" />
                            <x-dashboard.buttons.secondary onclick="hideModal('{{ $id }}')" type="button" :name="__('ui.no_cancel')" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
