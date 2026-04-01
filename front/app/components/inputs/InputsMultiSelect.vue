<script setup>
  const props = defineProps({
    modelValue: {
      type: Array,
      default: () => [],
    },
    options: {
      type: Array,
      default: () => [],
    },
    placeholder: {
      type: String,
    },
    max: {
      type: Number,
    },
  });

  const emit = defineEmits(['update:modelValue']);
  const selected = ref([...props.modelValue]);

  watch(
    () => props.modelValue,
    (val) => {
      selected.value = [...val];
    },
  );

  const toggleOption = (value) => {
    if (selected.value.includes(value)) {
      selected.value = selected.value.filter((v) => v !== value);
    } else {
      selected.value.push(value);

      if (props.max < selected.value.length) {
        selected.value = selected.value.splice(1);
      }
    }

    emit('update:modelValue', selected.value);
  };

  const isSelected = (value) => selected.value.includes(value);

  onMounted(() => {
    useFlowbite(() => {
      initDropdowns();
    });
  });
</script>

<template>
  <div class="relative w-full">
    <!-- Multiselect menu button -->
    <button id="dropdownMultiSelectButton" data-dropdown-toggle="dropdownMultiSelect" class="bg-transparent inline-flex border border-gray-300 text-gray-800 text-sm rounded-lg focus:ring-brand-500 focus:border-brand-500 justify-between w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500" type="button">
      <span class="truncate text-start">
        {{
          selected.length
            ? options
                .filter((o) => selected.includes(o.value))
                .map((o) => o.label)
                .join(', ')
            : placeholder
        }}
      </span>
      <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Multiselect menu -->
    <div id="dropdownMultiSelect" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-full max-h-80 overflow-y-auto border border-gray-200">
      <ul class="p-2 text-sm text-gray-700 space-y-1">
        <li v-for="opt in options" :key="opt.value" class="flex items-center p-2 rounded hover:bg-gray-50 cursor-pointer" @click="toggleOption(opt.value)">
          <input type="checkbox" class="w-4 h-4 text-brand-600 bg-gray-100 border-gray-300 rounded cursor-pointer focus:ring-brand-500" :checked="isSelected(opt.value)" readonly />
          <label class="ms-2 cursor-pointer select-none">{{ opt.label }}</label>
        </li>
      </ul>
    </div>
  </div>
</template>
