<template>
  <section class="py-12 md:py-32">
    <div class="container flex flex-col gap-10">
      <h3 v-text="$t('home.questions.title')" class="text-2xl md:text-3xl text-gray-800 font-medium"></h3>
      <QuestionCollapse heading-class="shadow-xs rounded-lg" title-class="text-gray-800 p-5 rounded-lg" body-class="p-5" />
    </div>
  </section>
</template>
