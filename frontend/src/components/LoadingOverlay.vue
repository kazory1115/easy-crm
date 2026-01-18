<template>
  <transition name="fade">
    <div v-if="show" class="loading-overlay">
      <div class="loading-overlay__panel">
        <UiSpinner size="lg" :label="label" />
        <p v-if="hint" class="mt-2 text-xs text-slate-500">{{ hint }}</p>
      </div>
    </div>
  </transition>
</template>

<script setup>
import UiSpinner from './UiSpinner.vue'

defineProps({
  show: { type: Boolean, default: false },
  label: { type: String, default: '載入中...' },
  hint: { type: String, default: '' }
})
</script>
