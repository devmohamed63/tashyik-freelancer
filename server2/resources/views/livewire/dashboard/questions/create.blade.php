<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.add_question')" />

        <form class="flex flex-col gap-5" wire:submit="store" x-data="{ loading: false }">

            <!-- Qestion -->
            <x-dashboard.inputs.default
                name="question"
                locale="ar"
                id="question-ar"
                wire:model="question"
                :required="true" />

            <!-- Answer ar -->
            <x-dashboard.inputs.textarea
                name="answer"
                wire:model="answer"
                locale="ar"
                id="answer-ar"
                rows="4"
                :required="true" />

            <!-- Submit button -->
            <x-dashboard.buttons.primary type="submit" :name="__('ui.add')" />

        </form>

    </div>
</div>
