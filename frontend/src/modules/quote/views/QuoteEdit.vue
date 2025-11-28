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
    <div class="max-w-7x1 mb-3 shadow-lg rounded-2xl border-white/20">
      <div class="max-w-7xl mx-auto px-6 sm:px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="mb-4 md:mb-0">
            <h1 class="text-3xl font-bold mb-2 tracking-tight">編輯報價單</h1>
            <p class="text-gray-600 mt-1">修改報價單內容</p>
          </div>
          <div class="flex gap-4">
            <button
              class="flex items-center px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
              @click="goBack"
            >
              <i class="fa-solid fa-arrow-left"></i>
              <span class="hidden sm:inline ml-2">返回</span>
            </button>
            <button
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
              @click="updateQuoteData"
              :disabled="loading"
            >
              <i class="fa-solid fa-save"></i>
              <span class="hidden sm:inline ml-2">{{ loading ? '儲存中...' : '儲存變更' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading && !currentQuote" class="text-center py-12">
      <i class="fa-solid fa-spinner fa-spin text-4xl text-blue-600"></i>
      <p class="mt-4 text-gray-600">載入中...</p>
    </div>

    <!-- Main Content -->
    <div v-else-if="currentQuote" class="max-w-7x1">
      <!-- Quotation Card -->
      <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-x-auto">
        <!-- Card Header -->
        <div class="bg-gray-100 p-8 text-gray-800">
          <div class="text-center mb-8">
            <h2 class="text-4xl font-bold mb-2 tracking-tight">報價單</h2>
            <div class="w-24 h-1 bg-gray-300 mx-auto rounded-full"></div>
          </div>
          <div class="grid md:grid-cols-2 gap-8">
            <div class="space-y-6">
              <div class="flex items-center">
                <label for="customerName" class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium">
                  客戶名稱:
                </label>
                <input
                  id="customerName"
                  type="text"
                  class="ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  v-model="formData.customerName"
                  placeholder="請輸入客戶名稱"
                  autocomplete="off"
                />
              </div>
              <div class="flex items-center">
                <label for="contactPhone" class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium">
                  聯絡電話:
                </label>
                <input
                  id="contactPhone"
                  type="text"
                  v-model="formData.contactPhone"
                  class="flex-1 ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  placeholder="請輸入聯絡電話"
                  autocomplete="off"
                />
              </div>
            </div>
            <div class="space-y-6">
              <div class="flex items-center">
                <label for="quotationNumber" class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium">
                  報價單號:
                </label>
                <input
                  id="quotationNumber"
                  type="text"
                  class="flex-1 ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  v-model="formData.quotationNumber"
                  placeholder="請輸入報價單號"
                  autocomplete="off"
                />
              </div>
              <div class="flex items-center">
                <label for="quotationDate" class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium">
                  報價日期:
                </label>
                <input
                  id="quotationDate"
                  type="date"
                  class="flex-1 ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  v-model="formData.date"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-8">
          <QuoteItemsTable
            v-model:items="formData.items"
            v-model:notes="formData.notes"
            :item-datas="itemDatas"
            :template-datas="templateDatas"
          />
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="text-center py-12">
      <i class="fa-solid fa-exclamation-triangle text-4xl text-red-600"></i>
      <p class="mt-4 text-gray-600">載入報價單失敗</p>
      <button @click="goBack" class="mt-4 px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
        返回列表
      </button>
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
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getItems } from '@/utils/dataManager'; // 一般項目暫時保留使用 LocalStorage
import { useQuote } from '../composables/useQuote';
import { useTemplate } from '../composables/useQuote';
import QuoteItemsTable from '../components/QuoteItemsTable.vue';

const route = useRoute();
const router = useRouter();

const { currentQuote, loading, fetchQuote, updateQuote } = useQuote();
const { templates, fetchTemplates } = useTemplate();

const quoteId = computed(() => route.params.id);

// Form data
const formData = ref({
  customerName: '',
  contactPhone: '',
  quotationNumber: '',
  date: '',
  notes: '',
  items: [],
});

const itemDatas = ref([]);
const templateDatas = computed(() => templates.value);

// 成功提示
const showSuccess = ref(false);
const successMessage = ref('');

// 載入資料
onMounted(async () => {
  // 載入一般項目
  itemDatas.value = getItems();

  // 載入範本
  try {
    await fetchTemplates();
  } catch (err) {
    console.error('載入範本失敗:', err);
  }

  // 載入報價單
  try {
    await fetchQuote(Number(quoteId.value));

    if (currentQuote.value) {
      // 填充表單資料（處理後端 snake_case 格式）
      formData.value = {
        customerName: currentQuote.value.customer_name || currentQuote.value.customerName || '',
        contactPhone: currentQuote.value.contact_phone || currentQuote.value.contactPhone || '',
        quotationNumber: currentQuote.value.project_name || currentQuote.value.quotationNumber || '',
        date: currentQuote.value.quote_date || currentQuote.value.date || '',
        notes: currentQuote.value.notes || '',
        items: currentQuote.value.items || [],
      };
    }
  } catch (err) {
    console.error('載入報價單失敗:', err);
  }
});

// 更新報價單
async function updateQuoteData() {
  if (!formData.value.customerName) {
    alert('請填寫客戶名稱');
    return;
  }

  // 過濾掉沒有填寫名稱的項目
  const validItems = formData.value.items.filter(item => item.name && item.name.trim() !== '');

  if (validItems.length === 0) {
    alert('請至少新增一個有效的報價項目（需填寫品名規格）');
    return;
  }

  const updateData = {
    customer_name: formData.value.customerName,
    contact_phone: formData.value.contactPhone,
    project_name: formData.value.quotationNumber,
    quote_date: formData.value.date,
    notes: formData.value.notes,
    items: validItems.map(item => ({
      type: item.type || 'input',
      name: item.name.trim(),
      description: item.description || null,
      quantity: item.quantity || 1,
      unit: item.unit || '式',
      price: item.price || 0,
      fields: item.fields || null,
      notes: item.notes || null
    })),
  };

  try {
    await updateQuote(Number(quoteId.value), updateData);
    showSuccessMessage('報價單更新成功！');

    // 2秒後返回列表
    setTimeout(() => {
      router.push('/quote/list');
    }, 2000);
  } catch (err) {
    console.error('更新報價單失敗:', err);
    alert('更新報價單失敗，請稍後再試');
  }
}

// 返回
function goBack() {
  router.back();
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
