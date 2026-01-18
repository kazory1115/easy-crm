<style scoped>
table {
  font-size: 0.9em;
}
.alignCenter {
  text-align: center;
}
</style>

<template>
  <div class="max-w-7xl mx-auto mt-10">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-gray-800">報價單詳情</h2>
          <p class="text-gray-600 mt-2">查看完整報價單資訊</p>
        </div>
        <div class="flex gap-2">
          <button
            v-if="currentQuote"
            @click="printQuote"
            class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors"
          >
            <i class="fa-solid fa-print mr-2"></i>
            列印
          </button>
          <button
            v-if="currentQuote"
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

    <!-- Loading -->
    <LoadingPanel v-if="loading" variant="skeleton" />

    <!-- Content -->
    <div v-else-if="currentQuote" class="app-card p-8" id="quote-detail-print">
      <!-- 基本資訊區塊 -->
      <div class="text-center mb-8">
        <h2 class="text-4xl font-bold mb-2 tracking-tight text-gray-800">報價單</h2>
        <div class="w-24 h-1 bg-blue-500 mx-auto rounded-full"></div>
      </div>

      <!-- 客戶與報價資訊 -->
      <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="bg-gray-50 p-6 rounded-lg">
          <h4 class="font-semibold text-gray-700 mb-4 text-lg border-b pb-2">
            <i class="fa-solid fa-user mr-2 text-blue-600"></i>
            客戶資訊
          </h4>
          <div class="space-y-3">
            <div class="flex">
              <span class="text-gray-600 w-24">客戶名稱:</span>
              <span class="font-semibold text-gray-800">{{ currentQuote.customerName }}</span>
            </div>
            <div v-if="currentQuote.contactPhone" class="flex">
              <span class="text-gray-600 w-24">聯絡電話:</span>
              <span class="font-semibold text-gray-800">{{ currentQuote.contactPhone }}</span>
            </div>
          </div>
        </div>

        <div class="bg-gray-50 p-6 rounded-lg">
          <h4 class="font-semibold text-gray-700 mb-4 text-lg border-b pb-2">
            <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
            報價資訊
          </h4>
          <div class="space-y-3">
            <div v-if="currentQuote.quotationNumber" class="flex">
              <span class="text-gray-600 w-24">報價單號:</span>
              <span class="font-semibold text-gray-800">{{ currentQuote.quotationNumber }}</span>
            </div>
            <div class="flex">
              <span class="text-gray-600 w-24">報價日期:</span>
              <span class="font-semibold text-gray-800">{{ formatDate(currentQuote.date) }}</span>
            </div>
            <div v-if="currentQuote.status" class="flex">
              <span class="text-gray-600 w-24">狀態:</span>
              <span class="font-semibold" :class="getStatusClass(currentQuote.status)">
                {{ getStatusText(currentQuote.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 項目表格 -->
      <div class="mb-8">
        <h4 class="font-semibold text-gray-700 mb-4 text-lg">
          <i class="fa-solid fa-list mr-2 text-blue-600"></i>
          報價項目
        </h4>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
          <table class="w-full min-w-[900px] text-sm">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="py-3 px-4 text-center font-medium text-gray-700 w-16">項次</th>
                <th class="py-3 px-4 text-center font-medium text-gray-700">品名規格</th>
                <th class="py-3 px-4 text-center font-medium text-gray-700 w-24">數量</th>
                <th class="py-3 px-4 text-center font-medium text-gray-700 w-24">單位</th>
                <th class="py-3 px-4 text-center font-medium text-gray-700 w-32">單價</th>
                <th class="py-3 px-4 text-center font-medium text-gray-700 w-32">複價</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr
                v-for="(item, index) in currentQuote.items"
                :key="index"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-4 py-3 text-center text-gray-600 font-semibold">{{ index + 1 }}</td>
                <td class="px-4 py-3 text-gray-800">
                  <div>
                    {{ getItemName(item.name) }}
                  </div>
                  <!-- 如果是模板項目，顯示詳細規格 -->
                  <div v-if="item.fields && item.fields.length > 0" class="mt-2 text-xs text-gray-600">
                    <div v-for="field in item.fields" :key="field.id" class="ml-4">
                      {{ field.label }}: {{ field.value }}
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-center text-gray-600">{{ item.quantity }}</td>
                <td class="px-4 py-3 text-center text-gray-600">{{ item.unit }}</td>
                <td class="px-4 py-3 text-right text-gray-600">{{ item.price.toLocaleString() }}</td>
                <td class="px-4 py-3 text-right text-gray-700 font-semibold">
                  {{ (item.quantity * item.price).toLocaleString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 總金額 -->
      <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mb-8">
        <div class="flex justify-between items-center">
          <span class="text-lg font-semibold text-gray-800">總金額：</span>
          <span class="text-3xl font-bold text-blue-600">
            {{ currentQuote.total.toLocaleString() }} 元
          </span>
        </div>
      </div>

      <!-- 備註 -->
      <div v-if="currentQuote.notes" class="bg-gray-50 p-6 rounded-lg mb-8">
        <h4 class="font-semibold text-gray-700 mb-3">
          <i class="fa-solid fa-note-sticky mr-2 text-blue-600"></i>
          備註
        </h4>
        <p class="text-gray-600 whitespace-pre-wrap">{{ currentQuote.notes }}</p>
      </div>

      <!-- 時間資訊 -->
      <div class="text-sm text-gray-500 border-t pt-4">
        <div class="flex justify-between">
          <span>建立時間：{{ formatDateTime(currentQuote.createdAt) }}</span>
          <span v-if="currentQuote.updatedAt !== currentQuote.createdAt">
            更新時間：{{ formatDateTime(currentQuote.updatedAt) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="app-card empty-state">
      <i class="fa-solid fa-exclamation-triangle text-6xl text-red-600 mb-4"></i>
      <h3 class="text-2xl font-bold text-gray-800 mb-2">載入失敗</h3>
      <p class="text-gray-600 mb-6">無法載入報價單資料，請稍後再試</p>
      <button
        @click="goToList"
        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        <i class="fa-solid fa-list mr-2"></i>
        返回列表
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import LoadingPanel from '@/components/LoadingPanel.vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuote } from '../composables/useQuote';

const route = useRoute();
const router = useRouter();

const { currentQuote, loading, error, fetchQuote } = useQuote();

const quoteId = computed(() => route.params.id);

// 載入報價單
onMounted(async () => {
  try {
    await fetchQuote(Number(quoteId.value));
  } catch (err) {
    console.error('載入報價單失敗:', err);
  }
});

// 格式化日期
function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  });
}

// 格式化日期時間
function formatDateTime(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// 取得項目名稱（處理 HTML）
function getItemName(name) {
  if (!name) return '';
  // 如果包含 HTML 標籤，移除它們
  const div = document.createElement('div');
  div.innerHTML = name;
  return div.textContent || div.innerText || name;
}

// 取得狀態文字
function getStatusText(status) {
  const statusMap = {
    draft: '草稿',
    sent: '已發送',
    approved: '已核准',
    rejected: '已拒絕',
  };
  return statusMap[status] || status;
}

// 取得狀態樣式
function getStatusClass(status) {
  const classMap = {
    draft: 'text-gray-600',
    sent: 'text-blue-600',
    approved: 'text-green-600',
    rejected: 'text-red-600',
  };
  return classMap[status] || 'text-gray-600';
}

// 編輯報價單
function editQuote() {
  router.push(`/quote/edit/${quoteId.value}`);
}

// 返回
function goBack() {
  router.back();
}

// 返回列表
function goToList() {
  router.push('/quote/list');
}

// 列印
function printQuote() {
  window.print();
}
</script>

<style>
/* 列印樣式 */
@media print {
  body * {
    visibility: hidden;
  }
  #quote-detail-print,
  #quote-detail-print * {
    visibility: visible;
  }
  #quote-detail-print {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }

  /* 隱藏按鈕 */
  button {
    display: none !important;
  }
}
</style>
