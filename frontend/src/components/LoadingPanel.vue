<template>
  <div class="loading-panel" :class="{ 'loading-panel--table': variant === 'table' }">
    <UiSpinner v-if="variant === 'spinner'" :label="label" :tone="tone" />
    <div v-else class="loading-skeleton">
      <div class="skeleton-line skeleton-title"></div>
      <div v-if="variant === 'table'" class="skeleton-table">
        <div v-for="row in rows" :key="row" class="skeleton-table-row">
          <span class="skeleton-cell w-20"></span>
          <span class="skeleton-cell flex-1"></span>
          <span class="skeleton-cell w-32"></span>
          <span class="skeleton-cell w-24"></span>
          <span class="skeleton-cell w-20"></span>
        </div>
      </div>
      <div v-else class="skeleton-list">
        <div v-for="row in rows" :key="row" class="skeleton-line"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import UiSpinner from './UiSpinner.vue'

defineProps({
  variant: { type: String, default: 'spinner' }, // spinner | skeleton | table
  label: { type: String, default: '載入中...' },
  rows: { type: Number, default: 6 },
  tone: { type: String, default: 'brand' }
})
</script>
