<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-gray-800">編輯報價單</h2>
          <p class="text-gray-600 mt-2">修改現有報價單內容</p>
        </div>
        <button
          @click="goBack"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
        >
          <i class="fa-solid fa-arrow-left mr-2"></i>
          返回
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
      <div class="text-center py-12">
        <div class="text-6xl mb-4">🚧</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">功能開發中</h3>
        <p class="text-gray-600 mb-6">
          報價單編輯功能正在開發中，敬請期待...
        </p>

        <!-- 參數資訊 -->
        <div class="bg-gray-50 rounded-lg p-4 max-w-md mx-auto">
          <p class="text-sm text-gray-600">
            <span class="font-semibold">報價單 ID:</span> {{ quoteId }}
          </p>
        </div>

        <!-- 臨時操作 -->
        <div class="mt-8 flex gap-4 justify-center">
          <button
            @click="goToList"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <i class="fa-solid fa-list mr-2"></i>
            查看報價單列表
          </button>
          <button
            @click="goToCreate"
            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            <i class="fa-solid fa-plus mr-2"></i>
            建立新報價單
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const quoteId = computed(() => route.params.id)

function goBack() {
  router.back()
}

function goToList() {
  router.push('/quote/list')
}

function goToCreate() {
  router.push('/quote/create')
}
</script>

<style scoped>
/* 動畫效果 */
.text-6xl {
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
