<script setup>
  const props = defineProps({
    modelValue: { type: String, default: '' },
    length: { type: Number, default: 6 },
  });

  const emit = defineEmits(['update:modelValue', 'complete']);

  const digits = ref(Array(props.length).fill(''));
  const inputRefs = ref([]);

  // Sync from parent
  watch(() => props.modelValue, (val) => {
    if (val) {
      const chars = val.split('');
      for (let i = 0; i < props.length; i++) {
        digits.value[i] = chars[i] || '';
      }
    } else {
      digits.value = Array(props.length).fill('');
    }
  }, { immediate: true });

  function emitValue() {
    const value = digits.value.join('');
    emit('update:modelValue', value);
    if (value.length === props.length) {
      emit('complete', value);
    }
  }

  function handleInput(index, event) {
    const val = event.target.value;

    // Only allow digits
    if (!/^\d*$/.test(val)) {
      event.target.value = digits.value[index];
      return;
    }

    // Handle paste of multiple digits
    if (val.length > 1) {
      const chars = val.split('');
      for (let i = 0; i < chars.length && index + i < props.length; i++) {
        digits.value[index + i] = chars[i];
      }
      const nextIndex = Math.min(index + chars.length, props.length - 1);
      inputRefs.value[nextIndex]?.focus();
      emitValue();
      return;
    }

    digits.value[index] = val;
    emitValue();

    // Auto-focus next
    if (val && index < props.length - 1) {
      inputRefs.value[index + 1]?.focus();
    }
  }

  function handleKeydown(index, event) {
    if (event.key === 'Backspace') {
      if (!digits.value[index] && index > 0) {
        digits.value[index - 1] = '';
        inputRefs.value[index - 1]?.focus();
        emitValue();
        event.preventDefault();
      } else {
        digits.value[index] = '';
        emitValue();
      }
    } else if (event.key === 'ArrowLeft' && index > 0) {
      inputRefs.value[index - 1]?.focus();
    } else if (event.key === 'ArrowRight' && index < props.length - 1) {
      inputRefs.value[index + 1]?.focus();
    }
  }

  function handlePaste(event) {
    event.preventDefault();
    const paste = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, props.length);
    const chars = paste.split('');
    for (let i = 0; i < props.length; i++) {
      digits.value[i] = chars[i] || '';
    }
    const focusIndex = Math.min(chars.length, props.length - 1);
    inputRefs.value[focusIndex]?.focus();
    emitValue();
  }

  function handleFocus(event) {
    event.target.select();
  }
</script>

<template>
  <div class="flex gap-2 sm:gap-3 justify-center" dir="ltr">
    <input
      v-for="(digit, index) in digits"
      :key="index"
      :ref="(el) => { if (el) inputRefs[index] = el }"
      type="text"
      inputmode="numeric"
      maxlength="6"
      :value="digit"
      @input="handleInput(index, $event)"
      @keydown="handleKeydown(index, $event)"
      @paste="handlePaste"
      @focus="handleFocus"
      class="otp-box"
      :class="{ 'otp-box--filled': digit }"
      autocomplete="one-time-code"
    />
  </div>
</template>

<style scoped>
.otp-box {
  width: 48px;
  height: 56px;
  border: 2px solid #d1d5db;
  border-radius: 12px;
  text-align: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  background: #f9fafb;
  outline: none;
  transition: all 0.2s ease;
  caret-color: transparent;
}

.otp-box:focus {
  border-color: var(--color-brand-500, #10b981);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  background: #fff;
  transform: scale(1.05);
}

.otp-box--filled {
  border-color: var(--color-brand-400, #34d399);
  background: #fff;
}

@media (min-width: 640px) {
  .otp-box {
    width: 56px;
    height: 64px;
    font-size: 1.75rem;
  }
}
</style>
