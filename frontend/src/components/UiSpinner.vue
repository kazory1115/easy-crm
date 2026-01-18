<template>
  <div class="inline-flex items-center gap-2" role="status" aria-live="polite">
    <svg
      class="animate-spin"
      :class="sizeClass"
      :style="{ color: toneColor }"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V2C6.477 2 2 6.477 2 12h2zm2 5.291A7.962 7.962 0 014 12H2c0 3.314 1.343 6.314 3.514 8.486L6 17.291z"
      ></path>
    </svg>
    <span v-if="label" class="text-sm text-slate-500">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: { type: String, default: 'md' },
  tone: { type: String, default: 'brand' },
  label: { type: String, default: '' }
})

const sizeClass = computed(() => {
  const map = {
    sm: 'h-4 w-4',
    md: 'h-6 w-6',
    lg: 'h-10 w-10'
  }
  return map[props.size] || map.md
})

const toneColor = computed(() => {
  return props.tone === 'muted' ? 'var(--text-400)' : 'var(--brand-600)'
})
</script>
