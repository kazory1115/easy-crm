<style scoped>
table {
  font-size: 0.9em; /* 或 90%、12px、或 1vw 都可以 */
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
            <h1 class="text-3xl font-bold mb-2 tracking-tight">報價管理系統</h1>
            <p class="text-gray-600 mt-1">專業報價單製作工具</p>
          </div>
          <div class="flex gap-4">
            <button
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
              @click="saveQuoteData"
              :disabled="loading || initialLoading"
            >
              <i class="fa-solid fa-floppy-disk"></i>
              <span class="hidden sm:inline ml-2">{{ loading ? '儲存中...' : '儲存報價單' }}</span>
            </button>

            <button
              class="flex items-center px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed"
              @click="printPage"
              :disabled="loading || initialLoading"
            >
              <!-- 印表機  icon -->
              <i class="fa-solid fa-print"></i>
              <span class="hidden sm:inline ml-2">列印</span>
            </button>

            <button
              class="flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed"
              @click="exportDoc"
              :disabled="loading || initialLoading"
            >
              <!-- Feather 文件 Icon -->
              <i class="fa-solid fa-file-word"></i>
              <span class="hidden sm:inline ml-2">匯出Word</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <LoadingPanel v-if="initialLoading" variant="skeleton" />

    <!-- Main Content -->
    <div v-else class="max-w-7x1">
      <!-- Quotation Card -->
      <div
        class="app-card overflow-x-auto"
      >
        <!-- Card Header -->
        <div class="bg-gray-100 p-8 text-gray-800">
          <div class="text-center mb-8">
            <h2 class="text-4xl font-bold mb-2 tracking-tight">報價單</h2>
            <div class="w-24 h-1 bg-gray-300 mx-auto rounded-full"></div>
          </div>
          <div class="grid md:grid-cols-2 gap-8">
            <div class="space-y-6">
              <div class="flex items-center">
                <label
                  for="customerName"
                  class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium"
                  >客戶名稱:</label
                >
                <input
                  id="customerName"
                  type="text"
                  class="ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  v-model="customerName"
                  placeholder="請輸入客戶名稱"
                  autocomplete="off"
                />
              </div>
              <div class="flex items-center">
                <label
                  for="contactPhone"
                  class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium"
                  >聯絡電話:</label
                >
                <input
                  id="contactPhone"
                  type="text"
                  v-model="contactPhone"
                  class="flex-1 ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  placeholder="請輸入聯絡電話"
                  autocomplete="off"
                />
              </div>
            </div>
            <div class="space-y-6">
              <div class="flex items-center">
                <label
                  for="quotationNumber"
                  class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium"
                  >報價單號:</label
                >
                <input
                  id="quotationNumber"
                  type="text"
                  class="flex-1 ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  v-model="quotationNumber"
                  placeholder="請輸入報價單號"
                  autocomplete="off"
                />
              </div>
              <div class="flex items-center">
                <label
                  for="quotationDate"
                  class="w-20 sm:w-24 md:w-32 text-gray-700 font-medium"
                  >報價日期:</label
                >
                <input
                  id="quotationDate"
                  type="date"
                  class="flex-1 ml-4 px-4 py-3 rounded-lg bg-white border border-gray-300 text-gray-800 placeholder-gray-400 focus:bg-gray-50 focus:border-blue-400 transition-all duration-200 w-full sm:w-auto flex-1 max-w-xs sm:max-w-md"
                  v-model="date"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-8">
          <QuoteItemsTable
            v-model:items="items"
            v-model:notes="notes"
            :item-datas="itemDatas"
            :template-datas="templateDatas"
          />
        </div>

        <!-- Card Footer -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-8">
          <div class="grid md:grid-cols-2 gap-8">
            <div>
              <h5 class="text-lg font-semibold text-gray-800 mb-4">
                製表人簽名
              </h5>
              <div
                class="h-24 border-2 border-dashed border-gray-300 rounded-lg bg-white flex items-center justify-center text-gray-400"
              ></div>
            </div>
            <div>
              <h5 class="text-lg font-semibold text-gray-800 mb-4">客戶簽名</h5>
              <div
                class="h-24 border-2 border-dashed border-gray-300 rounded-lg bg-white flex items-center justify-center text-gray-400"
              ></div>
            </div>
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
import { ref, computed, onMounted } from 'vue';
import LoadingPanel from '@/components/LoadingPanel.vue';
import { getItems } from '@/utils/dataManager'; // 一般項目暫時保留使用 LocalStorage
import { useQuote } from '../composables/useQuote';
import { useTemplate } from '../composables/useQuote';
import QuoteItemsTable from '../components/QuoteItemsTable.vue';

const { createQuote } = useQuote();
const { templates, fetchTemplates } = useTemplate();

const customerName = ref('');
const contactPhone = ref('');
const quotationNumber = ref('');
const notes = ref('');
const date = ref(new Date().toISOString().slice(0, 10)); // 預設當日 YYYY-MM-DD
const items = ref([]);

// 從 LocalStorage 載入項目範本資料
const itemDatas = ref([]);
const templateDatas = computed(() => templates.value);

// 載入狀態
const loading = ref(false);
const initialLoading = ref(true);

// 成功提示
const showSuccess = ref(false);
const successMessage = ref('');

// 載入資料
onMounted(async () => {
  try {
    initialLoading.value = true;

    // 從 LocalStorage 載入項目範本資料
    itemDatas.value = getItems();
    console.log('📦 [QuoteCreate] 載入的一般項目:', itemDatas.value);

    // 從 API 載入範本
    await fetchTemplates();
    console.log('📦 [QuoteCreate] 載入的模板:', templates.value);
    console.log('📦 [QuoteCreate] templateDatas computed:', templateDatas.value);

    // 初始化時至少要有一行
    if (items.value.length === 0) {
      items.value = [{
        id: 1,
        type: 'input',
        name: null,
        quantity: 1,
        unit: '式',
        price: 0,
        fields: [],
      }];
    }
  } catch (err) {
    console.error('載入資料失敗:', err);
    alert('載入資料失敗，請重新整理頁面');
  } finally {
    initialLoading.value = false;
  }
});

// 儲存報價單
async function saveQuoteData() {
  if (!customerName.value) {
    alert('請填寫客戶名稱');
    return;
  }

  // 過濾掉沒有填寫名稱的項目
  const validItems = items.value.filter(item => item.name && item.name.trim() !== '');

  if (validItems.length === 0) {
    alert('請至少新增一個有效的報價項目（需填寫品名規格）');
    return;
  }

  const quoteData = {
    customer_name: customerName.value,
    contact_phone: contactPhone.value,
    project_name: quotationNumber.value, // 後端使用 project_name
    quote_date: date.value,
    notes: notes.value,
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
    loading.value = true;
    await createQuote(quoteData);
    showSuccessMessage('報價單已儲存！');

    // 清空表單
    customerName.value = '';
    contactPhone.value = '';
    quotationNumber.value = '';
    notes.value = '';
    date.value = new Date().toISOString().slice(0, 10);

    // 重新初始化項目列表（至少一行）
    items.value = [{
      id: 1,
      type: 'input',
      name: null,
      quantity: 1,
      unit: '式',
      price: 0,
      fields: [],
    }];
  } catch (err) {
    console.error('儲存報價單失敗:', err);
    alert('儲存報價單失敗，請稍後再試');
  } finally {
    loading.value = false;
  }
}

// 成功提示
function showSuccessMessage(message) {
  successMessage.value = message;
  showSuccess.value = true;
  setTimeout(() => {
    showSuccess.value = false;
  }, 2000);
}

function generateHtmlContent() {
  // 計算總金額
  const total = items.value.reduce((sum, item) => sum + (item.quantity || 0) * (item.price || 0), 0);

  const itemsHtml = items.value.map((item, index) => {
    let specHtml = '';
    if (item.type === 'template' && item.fields && item.fields.length > 0) {
      const fieldLines = item.fields
        .map(f => `<div>${f.label}: ${f.value ?? ''}</div>`)
        .join('');
      specHtml = `<br/><small style="display: block; font-size: 0.8em; color: #555;">${fieldLines}</small>`;
    }
    return `
      <tr>
        <td class="alignCenter">${index + 1}</td>
        <td>${item.name || ''}${specHtml}</td>
        <td style="text-align: right;">${item.quantity || 0}</td>
        <td class="alignCenter">${item.unit || ''}</td>
        <td style="text-align: right;">${(item.price || 0).toLocaleString()}</td>
        <td style="text-align: right;">${((item.quantity || 0) * (item.price || 0)).toLocaleString()}</td>
      </tr>`;
  }).join('');

  return `
    <html>
    <head>
      <meta charset='utf-8'>
      <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color: #f2f2f2; text-align: center; }
        .alignCenter { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
      </style>
    </head>
    <body>
      <div class="header">
        <h2>報價單</h2>
        <p><strong>客戶名稱：</strong>${customerName.value || ''}</p>
        <p><strong>報價單號：</strong>${quotationNumber.value || ''} &nbsp;&nbsp; <strong>日期：</strong>${date.value || ''}</p>
      </div>
      <table>
        <tr>
          <th style="width: 5%">項次</th>
          <th style="width: 40%">品名規格</th>
          <th style="width: 8%">數量</th>
          <th style="width: 8%">單位</th>
          <th style="width: 15%">單價</th>
          <th style="width: 15%">複價</th>
        </tr>
        ${itemsHtml}
      </table>
      <p style="text-align: right; margin-top: 10px;"><strong>總金額：</strong>${total.toLocaleString()} 元</p>
      <div style="margin-top: 20px;">
        <strong>備註：</strong>
        <p style="white-space: pre-wrap;">${notes.value || ''}</p>
      </div>
      <div style="margin-top: 40px; display: table; width: 100%;">
        <div style="display: table-cell; width: 50%; padding-right: 20px;">
          <p>製表人簽名:</p>
          <div style="height: 80px; border-bottom: 1px solid #000;"></div>
        </div>
        <div style="display: table-cell; width: 50%; padding-left: 20px;">
          <p>客戶簽名:</p>
          <div style="height: 80px; border-bottom: 1px solid #000;"></div>
        </div>
      </div>
    </body>
    </html>
  `;
}

// 匯出 Word
function exportDoc() {
  const content = generateHtmlContent();
  const blob = new Blob(['\ufeff' + content], { type: 'application/msword' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = '報價單.doc';
  link.click();
}

// 列印（開新視窗載入內容並列印）
function printPage() {
  const content = generateHtmlContent();
  const printWindow = window.open('', '_blank');
  if (!printWindow) return alert('請允許彈跳視窗');
  printWindow.document.open();
  printWindow.document.write(content);
  printWindow.document.close();

  printWindow.focus();
  printWindow.onload = () => {
    printWindow.print();
    printWindow.close();
  };
}
</script>
