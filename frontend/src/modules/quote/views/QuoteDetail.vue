<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-gray-800">報價單詳情</h2>
          <p class="text-gray-600 mt-2">查看完整報價單資訊</p>
        </div>
        <div class="flex gap-2">
          <button
            @click="editQuote"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <i class="fa-solid fa-edit mr-2"></i>
            編輯
          </button>
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
          >
            <i class="fa-solid fa-arrow-left mr-2"></i>
            返回
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
      <div class="text-center py-12">
        <div class="text-6xl mb-4">📄</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">詳情頁面開發中</h3>
        <p class="text-gray-600 mb-6">
          報價單詳情頁面正在開發中...
          <br />
          目前可以在「報價單列表」中查看報價單摘要資訊。
        </p>

        <!-- 參數資訊 -->
        <div class="bg-gray-50 rounded-lg p-4 max-w-md mx-auto">
          <p class="text-sm text-gray-600">
            <span class="font-semibold">報價單 ID:</span> {{ quoteId }}
          </p>
        </div>

        <!-- 功能說明 -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6 max-w-2xl mx-auto text-left">
          <h4 class="font-semibold text-gray-800 mb-3">
            <i class="fa-solid fa-info-circle mr-2 text-blue-600"></i>
            即將推出的功能
          </h4>
          <ul class="space-y-2 text-sm text-gray-700">
            <li>
              <i class="fa-solid fa-check text-green-600 mr-2"></i>
              完整的報價單資訊展示
            </li>
            <li>
              <i class="fa-solid fa-check text-green-600 mr-2"></i>
              項目明細表格
            </li>
            <li>
              <i class="fa-solid fa-check text-green-600 mr-2"></i>
              匯出 PDF / Excel 功能
            </li>
            <li>
              <i class="fa-solid fa-check text-green-600 mr-2"></i>
              列印預覽
            </li>
            <li>
              <i class="fa-solid fa-check text-green-600 mr-2"></i>
              歷史版本記錄
            </li>
          </ul>
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

function editQuote() {
  router.push(`/quote/edit/${quoteId.value}`)
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
  animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}
</style>
