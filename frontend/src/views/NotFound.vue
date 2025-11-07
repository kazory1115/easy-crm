<template>
  <div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
      <!-- 404 圖示 -->
      <div class="mb-8">
        <span class="text-9xl">🔍</span>
      </div>

      <!-- 錯誤訊息 -->
      <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
      <h2 class="text-2xl font-semibold text-gray-600 mb-4">頁面不存在</h2>
      <p class="text-gray-500 mb-8 max-w-md mx-auto">
        抱歉，您訪問的頁面不存在或已被移除。
        <br />
        請檢查網址是否正確，或返回首頁。
      </p>

      <!-- 操作按鈕 -->
      <div class="flex gap-4 justify-center">
        <button
          @click="goBack"
          class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium"
        >
          <i class="fa-solid fa-arrow-left mr-2"></i>
          返回上一頁
        </button>
        <button
          @click="goHome"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
        >
          <i class="fa-solid fa-home mr-2"></i>
          返回首頁
        </button>
      </div>

      <!-- 建議連結 -->
      <div class="mt-12">
        <p class="text-sm text-gray-500 mb-4">您可能想要：</p>
        <div class="flex flex-wrap gap-2 justify-center">
          <router-link
            to="/quote/create"
            class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            📝 建立報價單
          </router-link>
          <router-link
            to="/quote/list"
            class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            📋 查看報價單列表
          </router-link>
          <router-link
            to="/quote/templates"
            class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            📁 範本管理
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

function goBack() {
  router.back()
}

function goHome() {
  router.push('/')
}
</script>

<style scoped>
/* 可選的動畫效果 */
.text-9xl {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}
</style>
