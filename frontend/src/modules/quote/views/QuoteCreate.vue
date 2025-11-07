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
              class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
              @click="saveQuoteData"
            >
              <i class="fa-solid fa-floppy-disk"></i>
              <span class="hidden sm:inline ml-2">儲存報價單</span>
            </button>

            <button
              class="flex items-center px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800"
              @click="printPage"
            >
              <!-- 印表機  icon -->
              <i class="fa-solid fa-print"></i>
              <span class="hidden sm:inline ml-2">列印</span>
            </button>

            <button
              class="flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              @click="exportDoc"
            >
              <!-- Feather 文件 Icon -->
              <i class="fa-solid fa-file-word"></i>
              <span class="hidden sm:inline ml-2">匯出Word</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7x1">
      <!-- Quotation Card -->
      <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-x-auto"
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
          <!-- Add Item Dropdown -->
          <div class="relative mb-3 text-right">
            <button
              @click="isOpen = !isOpen"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <i class="bi bi-plus-circle mr-2"></i> 新增項目
              <svg
                class="ml-2 h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <!-- Dropdown menu -->
            <div
              v-show="isOpen"
              @click.outside="isOpen = false"
              class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
            >
              <div class="py-1">
                <button
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  type="button"
                  @click.prevent="
                    addRow('drop');
                    isOpen = false;
                  "
                >
                  一般項目
                </button>
                <button
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  type="button"
                  @click.prevent="
                    addRow('template');
                    isOpen = false;
                  "
                >
                  自定義模板
                </button>
                <button
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  type="button"
                  @click.prevent="
                    addRow('input');
                    isOpen = false;
                  "
                >
                  填寫項目
                </button>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div
            class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm"
          >
            <table class="w-full min-w-[900px] text-sm">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th
                    class="py-3 px-2 text-center font-medium text-gray-700 w-[5%]"
                  >
                    項次
                  </th>
                  <th
                    class="py-3 px-2 text-center font-medium text-gray-700 w-[25%]"
                  >
                    品名規格
                  </th>
                  <th
                    class="py-3 px-2 text-center font-medium text-gray-700 w-[10%]"
                  >
                    數量
                  </th>
                  <th
                    class="py-3 px-2 text-center font-medium text-gray-700 w-[10%]"
                  >
                    單位
                  </th>
                  <th
                    class="py-3 px-2 text-center font-medium text-gray-700 w-[15%]"
                  >
                    單價
                  </th>
                  <th
                    class="py-3 px-2 text-center font-medium text-gray-700 w-[15%]"
                  >
                    複價
                  </th>
                  <th
                    class="py-3 px-2 text-center font-medium text-gray-700 w-[10%]"
                  >
                    功能
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="(item, index) in items"
                  :key="index"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-2 py-3 text-center text-gray-600 font-semibold">
                    {{ item.id }}
                  </td>

                  <td class="px-2 py-3">
                    <div class="space-y-1">
                      <input
                        v-if="item.type === 'input'"
                        type="text"
                        v-model="item.name"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="請輸入品名規格"
                      />
                      <select
                        v-if="item.type === 'drop'"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="selectRowData(item.id, $event, 'drop')"
                      >
                        <option value="" disabled selected>新增項目</option>
                        <option
                          v-for="dropItem in itemDatas"
                          :key="dropItem.id"
                          :value="dropItem.id"
                        >
                          {{ dropItem.name }}
                        </option>
                      </select>
                      <select
                        v-if="item.type === 'template'"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="selectRowData(item.id, $event, 'template')"
                      >
                        <option value="" disabled selected>選擇一個模板</option>
                        <option
                          v-for="templateItem in templateDatas"
                          :key="templateItem.id"
                          :value="templateItem.id"
                        >
                          {{ templateItem.name }}
                        </option>
                      </select>

                      <!-- 顯示模板的詳細資料 -->
                      <div v-if="item.type === 'template' && item.name" class="mt-2 text-xs text-gray-600 bg-gray-50 p-3 rounded-md border space-y-1">
                        <p class="font-bold text-sm text-gray-800">{{ item.name }}</p>
                        <div v-for="field in item.fields" :key="field.id" class="flex justify-between">
                          <span>{{ field.label }}:</span>
                          <span class="font-semibold">{{ field.value }}</span>
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="px-2 py-3">
                    <input
                      type="number"
                      v-model="item.quantity"
                      class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="數量"
                      list="quantityList"
                    />
                    <datalist id="quantityList">
                      <option v-for="n in 10" :key="n" :value="n">
                        {{ n }}
                      </option>
                    </datalist>
                  </td>

                  <td class="px-2 py-3">
                    <input
                      type="text"
                      v-model="item.unit"
                      class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="單位"
                    />
                  </td>

                  <td class="px-2 py-3">
                    <input
                      type="number"
                      v-model="item.price"
                      class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder="單價"
                    />
                  </td>

                  <td class="px-2 py-3 text-right text-gray-700 font-semibold">
                    {{ (item.quantity * item.price).toLocaleString() }}
                  </td>

                  <td class="px-2 py-3 text-center">
                    <button
                      class="p-2 text-red-500 hover:bg-red-50 rounded-md transition-all duration-200 disabled:opacity-50"
                      @click="delRow(item.id)"
                      :disabled="items.length <= 1"
                    >
                      <i class="bi bi-dash-circle text-lg"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Summary Section -->
          <div class="grid md:grid-cols-2 gap-8 mt-8">
            <!-- Notes Card -->
            <div
              class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 shadow-lg"
            >
              <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-sticky text-blue-500 text-xl"></i>
                <h5 class="text-lg font-semibold text-gray-800">備註</h5>
              </div>
              <textarea
                v-model="notes"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                rows="4"
                placeholder="請輸入備註事項..."
              ></textarea>
            </div>

            <!-- Calculation Card -->
            <div
              class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 shadow-lg"
            >
              <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-calculator text-blue-500 text-xl"></i>
                <h5 class="text-lg font-semibold text-gray-800">金額計算</h5>
              </div>
              <div class="space-y-3">
                <div class="flex justify-between text-gray-600">
                  <span>小計:</span>
                  <span class="font-medium">{{ total.toLocaleString() }}</span>
                </div>
                <hr class="border-gray-300" />
                <div class="flex justify-between text-xl font-bold">
                  <span class="text-gray-800">總金額:</span>
                  <span class="text-blue-600"
                    >{{ total.toLocaleString() }} 元</span
                  >
                </div>
              </div>
            </div>
          </div>
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
import { getItems, getTemplates, saveQuote } from '@/utils/dataManager';

const customerName = ref('');
const contactPhone = ref('');
const quotationNumber = ref('');
const notes = ref('');
const date = ref(new Date().toISOString().slice(0, 10)); // 預設當日 YYYY-MM-DD
const isOpen = ref(false);
const items = ref([]);

// 從 LocalStorage 載入項目範本資料
const itemDatas = ref([]);
const templateDatas = ref([]);

// 成功提示
const showSuccess = ref(false);
const successMessage = ref('');

// 載入資料
onMounted(() => {
  itemDatas.value = getItems();
  templateDatas.value = getTemplates();
  // 初始化時至少要有一行
  if (items.value.length === 0) {
    addRow('input');
  }
});

const total = computed(() =>
  items.value.reduce((sum, item) => sum + (item.quantity || 0) * (item.price || 0), 0)
);

function addRow(type) {
  const newId = items.value.length > 0 ? Math.max(...items.value.map((item) => item.id)) + 1 : 1;
  const newItem = {
    id: newId,
    type: type,
    name: null,
    quantity: 1,
    unit: '式',
    price: 0,
    fields: [], // 為模板類型準備
  };
  items.value.push(newItem);
}

function delRow(id) {
  if (items.value.length > 1) {
    items.value = items.value.filter((item) => item.id !== id);
  } else {
    // 如果只剩最後一項，則清空它而不是刪除
    const lastItem = items.value[0];
    lastItem.type = 'input';
    lastItem.name = null;
    lastItem.quantity = 1;
    lastItem.unit = '式';
    lastItem.price = 0;
    lastItem.fields = [];
  }
  // 重新排序 id
  items.value.forEach((item, idx) => {
    item.id = idx + 1;
  });
}

function selectRowData(id, event, type) {
  const selectedValue = event.target.value;
  const index = items.value.findIndex((item) => item.id === id);
  if (index === -1) return;

  let data, selectedItem;
  if (type === 'drop') {
    data = itemDatas.value;
    selectedItem = data.find((item) => item.id == selectedValue);
  } else if (type === 'template') {
    data = templateDatas.value;
    selectedItem = data.find((item) => item.id == selectedValue);
  }

  if (!selectedItem) return;

  // 更新 items.value 陣列中對應索引的資料
  const currentItem = items.value[index];
  currentItem.name = selectedItem.name;
  currentItem.fields = selectedItem.fields ? JSON.parse(JSON.stringify(selectedItem.fields)) : [];
  
  // 如果是一般項目，則帶入單價等資訊
  if (type === 'drop') {
    currentItem.quantity = selectedItem.quantity || 1;
    currentItem.unit = selectedItem.unit || '式';
    currentItem.price = selectedItem.price || 0;
  }
}

// 儲存報價單
function saveQuoteData() {
  if (!customerName.value || items.value.length === 0) {
    alert('請填寫客戶名稱並至少新增一個項目');
    return;
  }

  const quoteData = {
    customerName: customerName.value,
    contactPhone: contactPhone.value,
    quotationNumber: quotationNumber.value,
    date: date.value,
    notes: notes.value,
    items: items.value,
    total: total.value,
  };

  saveQuote(quoteData);
  showSuccessMessage('報價單已儲存！');
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
  const itemsHtml = items.value.map(item => {
    let specHtml = '';
    if (item.type === 'template' && item.fields && item.fields.length > 0) {
      specHtml = '<br/><small style="font-size: 0.8em; color: #555;">' + 
                   item.fields.map(f => `${f.label}: ${f.value}`).join(', ') + 
                   '</small>';
    }
    return `
      <tr>
        <td class="alignCenter">${item.id}</td>
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
      <p style="text-align: right; margin-top: 10px;"><strong>總金額：</strong>${total.value.toLocaleString()} 元</p>
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
