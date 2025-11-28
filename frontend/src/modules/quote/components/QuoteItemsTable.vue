<template>
  <div>
    <!-- Add Item Dropdown -->
    <div class="relative mb-3 text-right">
      <button
        @click="isOpen = !isOpen"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <i class="fas fa-plus-circle mr-2"></i> 新增項目
        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
            @click.prevent="addRow('drop'); isOpen = false;"
          >
            一般項目
          </button>
          <button
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            type="button"
            @click.prevent="addRow('template'); isOpen = false;"
          >
            自定義模板
          </button>
          <button
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            type="button"
            @click.prevent="addRow('input'); isOpen = false;"
          >
            填寫項目
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
      <table class="w-full min-w-[900px] text-sm">
        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
          <tr>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[3%]"></th>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[5%]">項次</th>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[25%]">品名規格</th>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[10%]">數量</th>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[10%]">單位</th>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[15%]">單價</th>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[15%]">複價</th>
            <th class="py-3 px-2 text-center font-medium text-gray-700 w-[7%]">功能</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr
            v-for="(item, index) in props.items"
            :key="index"
            :draggable="true"
            @dragstart="handleDragStart(index, $event)"
            @dragover.prevent="handleDragOver(index, $event)"
            @drop="handleDrop(index, $event)"
            @dragend="handleDragEnd"
            :class="[
              'transition-colors cursor-move',
              draggedIndex === index ? 'opacity-50 bg-blue-50' : 'hover:bg-gray-50',
              dragOverIndex === index && draggedIndex !== index ? 'border-t-2 border-blue-500' : ''
            ]"
          >
            <!-- 拖拉手柄 -->
            <td class="px-2 py-3 text-center cursor-grab active:cursor-grabbing">
              <i class="fas fa-grip-vertical text-gray-400 text-lg"></i>
            </td>

            <!-- 項次 -->
            <td class="px-2 py-3 text-center text-gray-600 font-semibold">
              {{ index + 1 }}
            </td>

            <!-- 品名規格 -->
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
                  <option value="" disabled :selected="!item.name">
                    {{ item.name || '新增項目' }}
                  </option>
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
                  <option value="" disabled :selected="!item.name">
                    {{ item.name || '選擇一個模板' }}
                  </option>
                  <option
                    v-for="templateItem in templateDatas"
                    :key="templateItem.id"
                    :value="templateItem.id"
                  >
                    {{ templateItem.name }}
                  </option>
                </select>

                <!-- 顯示模板的詳細欄位（簡潔版） -->
                <div v-if="item.type === 'template' && item.fields && item.fields.length > 0" class="mt-2 space-y-1.5">
                  <div v-for="(field, fieldIndex) in item.fields" :key="field.id" class="flex items-center gap-2 text-xs">
                    <span class="text-gray-600 font-medium min-w-[60px]">{{ field.label }}:</span>
                    <input
                      type="text"
                      v-model="item.fields[fieldIndex].value"
                      @input="updateTemplateField(item.id, fieldIndex, $event.target.value)"
                      class="flex-1 px-2 py-1 border border-gray-200 rounded focus:border-blue-400 focus:ring-1 focus:ring-blue-400 bg-white"
                      :placeholder="`請輸入${field.label}`"
                    />
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
                <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
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

            <!-- 功能 -->
            <td class="px-2 py-3 text-center">
              <button
                @click.stop="delRow(index)"
                :disabled="props.items.length <= 1"
                class="inline-flex items-center justify-center p-2 text-red-600 hover:bg-red-50 hover:text-red-700 rounded-md transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                title="刪除"
              >
                <i class="fas fa-trash text-lg"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Summary Section -->
    <div class="grid md:grid-cols-2 gap-8 mt-8">
      <!-- Notes Card -->
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-2 mb-4">
          <i class="fas fa-sticky-note text-blue-500 text-xl"></i>
          <h5 class="text-lg font-semibold text-gray-800">備註</h5>
        </div>
        <textarea
          :value="notes"
          @input="$emit('update:notes', $event.target.value)"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
          rows="4"
          placeholder="請輸入備註事項..."
        ></textarea>
      </div>

      <!-- Calculation Card -->
      <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 shadow-lg">
        <div class="flex items-center gap-2 mb-4">
          <i class="fas fa-calculator text-blue-500 text-xl"></i>
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
            <span class="text-blue-600">{{ total.toLocaleString() }} 元</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  itemDatas: {
    type: Array,
    default: () => [],
  },
  templateDatas: {
    type: Array,
    default: () => [],
  },
  notes: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:items', 'update:notes']);

const isOpen = ref(false);
const draggedIndex = ref(null);
const dragOverIndex = ref(null);

// 計算總金額
const total = computed(() =>
  props.items.reduce((sum, item) => sum + (item.quantity || 0) * (item.price || 0), 0)
);

// 新增項目
function addRow(type) {
  const currentItems = [...props.items];
  const newId = currentItems.length > 0
    ? Math.max(...currentItems.map((item) => item.id)) + 1
    : 1;

  const newItem = {
    id: newId,
    type: type,
    name: null,
    quantity: 1,
    unit: '式',
    price: 0,
    fields: [],
  };

  emit('update:items', [...currentItems, newItem]);
}

// 拖拉排序相關函數
function handleDragStart(index, event) {
  draggedIndex.value = index;
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/html', event.target.innerHTML);
}

function handleDragOver(index, event) {
  if (draggedIndex.value === null) return;
  dragOverIndex.value = index;
}

function handleDrop(index, event) {
  event.preventDefault();

  if (draggedIndex.value === null || draggedIndex.value === index) {
    return;
  }

  const currentItems = [...props.items];
  const draggedItem = currentItems[draggedIndex.value];

  // 移除被拖動的項目
  currentItems.splice(draggedIndex.value, 1);

  // 在新位置插入
  currentItems.splice(index, 0, draggedItem);

  // 重新分配 ID
  const reordered = currentItems.map((item, idx) => ({
    ...item,
    id: idx + 1
  }));

  emit('update:items', reordered);
}

function handleDragEnd() {
  draggedIndex.value = null;
  dragOverIndex.value = null;
}

// 刪除項目
function delRow(index) {
  const currentItems = [...props.items];

  if (currentItems.length <= 1) {
    // 如果只剩最後一項，則清空它而不是刪除
    const resetItem = {
      ...currentItems[0],
      type: 'input',
      name: null,
      quantity: 1,
      unit: '式',
      price: 0,
      fields: [],
    };
    emit('update:items', [resetItem]);
    return;
  }

  // 刪除指定索引的項目
  currentItems.splice(index, 1);

  // 重新分配 ID
  const reordered = currentItems.map((item, idx) => ({
    ...item,
    id: idx + 1
  }));

  emit('update:items', reordered);
}

// 選擇項目資料
function selectRowData(id, event, type) {
  const selectedValue = event.target.value;
  const currentItems = [...props.items];
  const index = currentItems.findIndex((item) => item.id === id);
  if (index === -1) return;

  let data, selectedItem;
  if (type === 'drop') {
    data = props.itemDatas;
    selectedItem = data.find((item) => item.id === Number(selectedValue));
  } else if (type === 'template') {
    data = props.templateDatas;

    // 調試：輸出模板資料
    console.log('🔍 [選擇模板] 可用模板列表:', props.templateDatas);
    console.log('🔍 [選擇模板] 選中的模板ID:', selectedValue, '類型:', typeof selectedValue);

    // 嘗試將 selectedValue 轉換為與後端 ID 相同的類型
    // 先嘗試字串比對，再嘗試數字比對
    selectedItem = data.find((item) => {
      console.log('  比對:', item.id, '(類型:', typeof item.id, ') vs', selectedValue, '(類型:', typeof selectedValue, ')');
      return item.id == selectedValue; // 使用寬鬆比較 == 而不是 ===
    });

    console.log('🔍 [選擇模板] 找到的模板:', selectedItem);
  }

  if (!selectedItem) {
    console.error('❌ [選擇模板] 找不到模板！');
    return;
  }

  // 處理模板欄位：轉換後端格式 (field_label, field_value) 到前端格式 (label, value)
  let processedFields = [];
  if (type === 'template' && selectedItem.fields) {
    console.log('🔍 [選擇模板] 原始欄位資料:', selectedItem.fields);

    processedFields = selectedItem.fields.map(field => ({
      id: field.id,
      label: field.field_label || field.label,
      value: field.field_value || field.value || '',
    }));

    console.log('✅ [選擇模板] 轉換後的欄位:', processedFields);
  } else if (selectedItem.fields) {
    processedFields = JSON.parse(JSON.stringify(selectedItem.fields));
  }

  // 創建更新後的項目
  const updatedItem = {
    ...currentItems[index],
    name: selectedItem.name,
    fields: processedFields,
  };

  console.log('📝 [選擇模板] 更新後的項目:', updatedItem);

  // 如果是一般項目，則帶入單價等資訊
  if (type === 'drop') {
    updatedItem.quantity = selectedItem.quantity || 1;
    updatedItem.unit = selectedItem.unit || '式';
    updatedItem.price = selectedItem.price || 0;
  }

  // 更新陣列並 emit
  currentItems[index] = updatedItem;
  emit('update:items', currentItems);
}

// 更新模板欄位值
function updateTemplateField(itemId, fieldIndex, value) {
  const currentItems = [...props.items];
  const itemIndex = currentItems.findIndex((item) => item.id === itemId);

  if (itemIndex === -1) return;

  // 更新欄位值
  if (currentItems[itemIndex].fields && currentItems[itemIndex].fields[fieldIndex]) {
    currentItems[itemIndex].fields[fieldIndex].value = value;
    emit('update:items', currentItems);
  }
}
</script>

<style scoped>
table {
  font-size: 0.9em;
}
</style>
