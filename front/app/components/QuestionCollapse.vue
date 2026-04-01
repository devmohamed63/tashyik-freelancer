<script setup>
  defineProps(['headingClass', 'titleClass', 'bodyClass']);

  const questions = ref(null);

  onMounted(async () => {
    try {
      const response = await useApiFetch('/general/questions');

      questions.value = response.data;
    } catch (error) {
      console.error('Failed to get questions:', error);
    }

    useFlowbite(() => {
      initAccordions();
    });
  });
</script>

<template>
  <div id="accordion-collapse" data-accordion="collapse" class="flex flex-col gap-5">
    <div v-for="question in questions" :key="question.id" :class="headingClass">
      <h3 :id="`questions-collapse-heading-${question.id}`">
        <button type="button" :class="titleClass" class="bg-white flex items-center justify-between w-full font-medium" :data-accordion-target="`#questions-collapse-body-${question.id}`" aria-expanded="false" :aria-controls="`questions-collapse-body-${question.id}`">
          <span v-text="question.title" class="text-start"></span>
          <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0 stroke-gray-500" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.8298 5.8314L5.8314 0.833008L0.833008 5.8314" stroke-width="1.66613" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </h3>
      <div :id="`questions-collapse-body-${question.id}`" class="hidden" :aria-labelledby="`questions-collapse-heading-${question.id}`">
        <p v-text="question.answer" :class="bodyClass" class="pt-0 -mt-3 bg-white rounded-b-lg text-gray-500"></p>
      </div>
    </div>
  </div>
</template>
