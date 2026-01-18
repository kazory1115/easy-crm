<style scoped>
table {
  font-size: 0.9em;
}
.alignCenter {
  text-align: center;
}
</style>

<template>
  <div id="app" class="max-w-7xl mx-auto mt-10">
    <!-- Header -->
    <div class="max-w-7x1 mb-6 shadow-lg rounded-2xl border-white/20">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <h2
          class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent text-center"
        >
          歷史報價單
        </h2>
        <p class="text-gray-600 text-center mt-2">查看和管理已儲存的報價單</p>
      </div>
    </div>

    <!-- 報價單列表 -->
    <div class="app-card p-8">
      <LoadingPanel v-if="loading" variant="skeleton" />
      <div v-else-if="quotes.length === 0" class="empty-state">
        <i class="fa-solid fa-inbox text-6xl mb-4"></i>
        <p class="text-xl">尚無報價單紀錄</p>
        <p class="text-sm mt-2">請到「報價管理系統」建立報價單</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="quote in quotes"
          :key="quote.id"
          class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <h3 class="text-xl font-bold text-gray-800 mb-2">
                <i class="fa-solid fa-file-invoice mr-2 text-blue-600"></i>
                {{ quote.customer_name || quote.customerName }}
              </h3>
              <div class="grid md:grid-cols-2 gap-2 text-sm text-gray-600">
                <div>
                  <i class="fa-solid fa-calendar mr-2"></i>
                  <span>日期：{{ formatDate(quote.quote_date || quote.date) }}</span>
                </div>
                <div v-if="quote.quote_number || quote.quotationNumber">
                  <i class="fa-solid fa-hashtag mr-2"></i>
                  <span>單號：{{ quote.quote_number || quote.quotationNumber }}</span>
                </div>
                <div v-if="quote.contact_phone || quote.contactPhone">
                  <i class="fa-solid fa-phone mr-2"></i>
                  <span>電話：{{ quote.contact_phone || quote.contactPhone }}</span>
                </div>
                <div>
                  <i class="fa-solid fa-dollar-sign mr-2"></i>
                  <span class="font-semibold text-blue-600"
                    >總金額：{{ (quote.total || 0).toLocaleString() }} 元</span
                  >
                </div>
              </div>
            </div>
            <div class="flex gap-2">
              <button
                @click="viewQuote(quote)"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                title="查看詳情"
              >
                <i class="fa-solid fa-eye"></i>
              </button>
              <button
                @click="editQuote(quote.id)"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                title="編輯"
              >
                <i class="fa-solid fa-edit"></i>
              </button>
              <button
                @click="deleteQuoteData(quote.id)"
                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
                title="刪除"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>

          <!-- 項目摘要 -->
          <div class="mt-4 bg-gray-50 p-4 rounded-lg">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">
              項目摘要（共 {{ quote.items.length }} 項）
            </h4>
            <div class="text-xs text-gray-600 space-y-1">
              <div
                v-for="(item, idx) in quote.items.slice(0, 3)"
                :key="idx"
                class="flex justify-between"
              >
                <span>{{ idx + 1 }}. {{ getItemName(item.name) }}</span>
                <span class="font-semibold"
                  >{{ (item.quantity * item.price).toLocaleString() }} 元</span
                >
              </div>
              <div v-if="quote.items.length > 3" class="text-gray-400 italic">
                ...還有 {{ quote.items.length - 3 }} 個項目
              </div>
            </div>
          </div>

          <!-- 備註 -->
          <div v-if="quote.notes" class="mt-4 text-sm">
            <span class="font-semibold text-gray-700">備註：</span>
            <span class="text-gray-600">{{ quote.notes }}</span>
          </div>

          <!-- 時間戳記 -->
          <div class="mt-4 text-xs text-gray-400">
            建立時間：{{ formatDateTime(quote.created_at || quote.createdAt) }}
            <span v-if="(quote.updated_at || quote.updatedAt) !== (quote.created_at || quote.createdAt)" class="ml-4">
              更新時間：{{ formatDateTime(quote.updated_at || quote.updatedAt) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 查看詳情對話框 -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeDetailModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto p-8"
      >
        <div class="flex justify-between items-start mb-6">
          <h3 class="text-2xl font-bold text-gray-800">報價單詳情</h3>
          <button
            @click="closeDetailModal"
            class="text-gray-400 hover:text-gray-600 text-2xl"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div v-if="selectedQuote">
          <!-- 基本資訊 -->
          <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-gray-700 mb-3">客戶資訊</h4>
              <div class="space-y-2 text-sm">
                <div>
                  <span class="text-gray-600">客戶名稱：</span>
                  <span class="font-semibold">{{ selectedQuote.customer_name || selectedQuote.customerName }}</span>
                </div>
                <div v-if="selectedQuote.contact_phone || selectedQuote.contactPhone">
                  <span class="text-gray-600">聯絡電話：</span>
                  <span class="font-semibold">{{ selectedQuote.contact_phone || selectedQuote.contactPhone }}</span>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-gray-700 mb-3">報價資訊</h4>
              <div class="space-y-2 text-sm">
                <div v-if="selectedQuote.quote_number || selectedQuote.quotationNumber">
                  <span class="text-gray-600">報價單號：</span>
                  <span class="font-semibold">{{ selectedQuote.quote_number || selectedQuote.quotationNumber }}</span>
                </div>
                <div>
                  <span class="text-gray-600">報價日期：</span>
                  <span class="font-semibold">{{ formatDate(selectedQuote.quote_date || selectedQuote.date) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 項目表格 -->
          <div class="mb-6">
            <h4 class="font-semibold text-gray-700 mb-3">報價項目</h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
              <table class="w-full min-w-[900px] text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                  <tr>
                    <th class="py-3 px-4 text-center font-medium text-gray-700 w-16">
                      項次
                    </th>
                    <th class="py-3 px-4 text-center font-medium text-gray-700">
                      品名規格
                    </th>
                    <th class="py-3 px-4 text-center font-medium text-gray-700 w-24">
                      數量
                    </th>
                    <th class="py-3 px-4 text-center font-medium text-gray-700 w-24">
                      單位
                    </th>
                    <th class="py-3 px-4 text-center font-medium text-gray-700 w-32">
                      單價
                    </th>
                    <th class="py-3 px-4 text-center font-medium text-gray-700 w-32">
                      複價
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr
                    v-for="(item, index) in selectedQuote.items"
                    :key="index"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-4 py-3 text-center text-gray-600 font-semibold">
                      {{ index + 1 }}
                    </td>
                    <td class="px-4 py-3 text-gray-800" v-html="item.name"></td>
                    <td class="px-4 py-3 text-center text-gray-600">
                      {{ item.quantity }}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">
                      {{ item.unit }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">
                      {{ item.price.toLocaleString() }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700 font-semibold">
                      {{ (item.quantity * item.price).toLocaleString() }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 總金額 -->
          <div class="bg-blue-50 p-4 rounded-lg mb-6">
            <div class="flex justify-between items-center">
              <span class="text-lg font-semibold text-gray-800">總金額：</span>
              <span class="text-2xl font-bold text-blue-600"
                >{{ (selectedQuote.total || 0).toLocaleString() }} 元</span
              >
            </div>
          </div>

          <!-- 備註 -->
          <div v-if="selectedQuote.notes" class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-700 mb-2">備註</h4>
            <p class="text-sm text-gray-600">{{ selectedQuote.notes }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 成功提示 -->
    <div
      v-if="showSuccess"
      class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
    >
      <i class="fa-solid fa-check-circle mr-2"></i>{{ successMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import LoadingPanel from '@/components/LoadingPanel.vue';
import { useRouter } from 'vue-router';
import { useQuote } from '../composables/useQuote';

const router = useRouter();
const { quotes, loading, error, fetchQuotes, deleteQuote: deleteQuoteApi } = useQuote();
const selectedQuote = ref(null);
const showDetailModal = ref(false);

// 成功提示
const showSuccess = ref(false);
const successMessage = ref('');

// 載入資料
onMounted(() => {
  loadQuotes();
});

async function loadQuotes() {
  try {
    await fetchQuotes({ sort_by: 'created_at', sort_order: 'desc' });
  } catch (err) {
    console.error('載入報價單失敗:', err);
  }
}

// 查看報價單詳情
function viewQuote(quote) {
  selectedQuote.value = quote;
  showDetailModal.value = true;
}

// 編輯報價單
function editQuote(id) {
  router.push(`/quote/edit/${id}`);
}

// 關閉詳情對話框
function closeDetailModal() {
  showDetailModal.value = false;
  selectedQuote.value = null;
}

// 刪除報價單
async function deleteQuoteData(id) {
  if (confirm('確定要刪除此報價單嗎？此操作無法復原。')) {
    try {
      await deleteQuoteApi(id);
      await loadQuotes();
      showSuccessMessage('報價單已刪除！');
    } catch (err) {
      console.error('刪除報價單失敗:', err);
    }
  }
}

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

// 取得項目名稱（移除 HTML 標籤）
function getItemName(name) {
  if (!name) return '';
  // 移除 HTML 標籤，只保留文字
  const div = document.createElement('div');
  div.innerHTML = name;
  return div.textContent || div.innerText || name;
}

// 成功提示
function showSuccessMessage(message) {
  successMessage.value = message;
  showSuccess.value = true;
  setTimeout(() => {
    showSuccess.value = false;
  }, 2000);
}
</script>
