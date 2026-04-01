<script setup>
  import { Modal } from 'flowbite';

  const targetElement = ref(null);
  const modal = ref(null);
  const emit = defineEmits(['hide-modal']);
  const rate = ref(0);
  const notes = ref('');
  const loader = ref(false);
  const errors = ref({});

  const props = defineProps({
    order: {
      type: Object,
      required: true,
    },
    reviewModal: {
      type: Boolean,
      required: true,
    },
  });

  async function handleReview() {
    if (loader.value) return;

    loader.value = true;

    try {
      await useApiFetch('/reviews', {
        method: 'POST',
        body: {
          service_provider: props.order.service_provider.id,
          rating: rate.value,
          body: notes.value,
        },
      });
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        errors.value = error.data.errors.errors;
        loader.value = false;
        return;
      } else {
        return console.error('Failed to save review:', error);
      }
    }

    reloadNuxtApp();
  }

  watch(
    () => props.reviewModal,
    async () => {
      if (props.reviewModal) {
        modal.value.show();
      } else {
        modal.value.hide();
      }
    },
  );

  onMounted(() => {
    if (targetElement.value) {
      modal.value = new Modal(targetElement.value, {
        onHide: () => {
          emit('hide-modal');
        },
        backdropClasses: 'modal-backdrop',
      });
    }
  });
</script>

<template>
  <div v-show="reviewModal" class="w-full h-full fixed z-30 start-0 top-0 bg-black/40"></div>

  <div ref="targetElement" id="rateModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-xl max-h-full">
      <!-- Modal content -->
      <form @submit.prevent="handleReview" class="relative bg-white rounded-2xl shadow-sm dark:bg-gray-700">
        <!-- Modal header -->
        <div class="inline-flex gap-2 items-center p-4 md:p-6 border-b w-full rounded-t border-gray-200">
          <button @click="$emit('hide-modal')" id="closeButton" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-700 rounded-lg text-sm p-1.5 inline-flex items-center">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
          </button>
          <span v-text="$t('review.title')" class="text-lg text-gray-800 font-medium"></span>
        </div>

        <!-- Modal body -->
        <div class="p-6 md:p-8 flex flex-col gap-5">
          <!-- Order details -->
          <div class="border border-gray-200 rounded-xl p-4 md:p-7">
            <div class="inline-flex items-center justify-between gap-3 w-full">
              <div class="flex flex-col gap-1">
                <span v-text="order.service_provider?.name" class="text-gray-800 font-medium"></span>
                <span v-text="order.service?.name" class="text-sm text-gray-500"></span>
                <span v-text="`${$t('review.price')}: ${order.subtotal} ${order.currency}`" class="text-sm text-gray-500"></span>
              </div>
              <img class="w-16 md:w-20 rounded-lg" src="/images/service-provider.webp" alt="..." />
            </div>
          </div>

          <!-- Rate -->
          <div class="border border-gray-200 rounded-xl p-4 md:p-7 flex flex-col items-center text-center gap-4">
            <span v-text="$t('review.rate')" class="text-gray-800 font-medium"></span>
            <InputsInlineRating v-model="rate" class="w-7 h-7" />
            <InputsError :message="errors?.rating?.[0]" />
          </div>

          <!-- Notes -->
          <div class="border border-gray-200 rounded-xl p-4 md:p-7 flex flex-col gap-4">
            <label v-text="$t('review.notes')" for="notes" class="text-gray-800 font-medium"></label>
            <textarea v-model="notes" id="notes" :placeholder="$t('review.notes_placeholder')" rows="4" class="w-full bg-white p-3 rounded-lg border border-gray-200 placeholder:text-gray-400 font-light focus:ring-2 ring-brand-100"></textarea>
            <InputsError :message="errors?.body?.[0]" />
          </div>
        </div>
        <div class="border-t border-gray-200 w-full flex flex-col gap-3 items-center p-4 md:p-6">
          <ButtonsFilled class="w-full max-w-xs" type="submit" :disabled="loader">
            {{ $t('review.actions.send') }}
          </ButtonsFilled>
          <button @click="$emit('hide-modal')" v-text="$t('review.actions.skip')" class="text-gray-500 text-center w-fit"></button>
        </div>
      </form>
    </div>
  </div>
</template>
