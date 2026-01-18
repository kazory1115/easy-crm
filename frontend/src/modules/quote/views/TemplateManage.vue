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
          項目範本管理
        </h2>
        <p class="text-gray-600 text-center mt-2">管理報價單的項目範本資料</p>
      </div>
    </div>
    <!-- Tabs -->
    <div class="mb-6">
      <div class="flex border-b border-gray-300">
        <button
          @click="activeTab = 'items'"
          :class="[
            'px-6 py-3 font-semibold transition-all duration-200',
            activeTab === 'items'
              ? 'border-b-2 border-blue-600 text-blue-600'
              : 'text-gray-600 hover:text-blue-500',
          ]"
        >
          <i class="fa-solid fa-list mr-2"></i>一般項目
        </button>
        <button
          @click="activeTab = 'templates'"
          :class="[
            'px-6 py-3 font-semibold transition-all duration-200',
            activeTab === 'templates'
              ? 'border-b-2 border-blue-600 text-blue-600'
              : 'text-gray-600 hover:text-blue-500',
          ]"
        >
          <i class="fa-solid fa-puzzle-piece mr-2"></i>自定義模板
        </button>
      </div>
    </div>
    <!-- 一般項目表單 -->
    <div v-if="activeTab === 'items'" class="transition-opacity duration-300">
      <form @submit.prevent="addItem" class="app-card p-8 mb-8">
        <h3 class="text-xl font-bold mb-6 text-gray-800">新增一般項目</h3>
        <div class="grid md:grid-cols-4 gap-6 mb-6">
          <div>
            <label for="item-name" class="block mb-2 text-gray-700 font-medium"
              >品名規格</label
            >
            <input
              id="item-name"
              v-model="itemForm.name"
              type="text"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-400 transition-all duration-200"
              placeholder="請輸入品名規格"
              required
            />
          </div>
          <div>
            <label
              for="item-quantity"
              class="block mb-2 text-gray-700 font-medium"
              >數量</label
            >
            <input
              id="item-quantity"
              v-model.number="itemForm.quantity"
              type="number"
              min="1"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-400 transition-all duration-200"
              placeholder="請輸入數量"
              required
            />
          </div>
          <div>
            <label for="item-unit" class="block mb-2 text-gray-700 font-medium"
              >單位</label
            >
            <input
              id="item-unit"
              v-model="itemForm.unit"
              type="text"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-400 transition-all duration-200"
              placeholder="輸入單位"
              required
            />
          </div>
          <div>
            <label for="item-price" class="block mb-2 text-gray-700 font-medium"
              >單價</label
            >
            <input
              id="item-price"
              v-model.number="itemForm.price"
              type="number"
              min="0"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-400 transition-all duration-200"
              placeholder="請輸入單價"
              required
            />
          </div>
        </div>
        <div class="flex justify-between items-center">
          <div class="text-gray-600 font-semibold">
            複價：<span class="text-blue-600">{{
              (itemForm.quantity * itemForm.price || 0).toLocaleString()
            }}</span>
          </div>
          <button
            type="submit"
            class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-2 rounded-lg shadow transition font-semibold"
          >
            <i class="fa-solid fa-plus mr-2"></i>新增
          </button>
        </div>
      </form>
      <!-- 一般項目列表 -->
      <div class="app-card p-8">
        <h3 class="text-2xl font-bold mb-6 text-blue-700">一般項目列表</h3>
        <LoadingPanel v-if="loading" variant="table" />
        <div v-else-if="items.length === 0" class="empty-state">
          查無項目資料
        </div>
        <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
          <table class="w-full min-w-[900px] text-sm">
            <thead>
              <tr
                class="bg-gradient-to-r from-blue-100 to-blue-200 border-b border-blue-300"
              >
                <th class="py-3 text-center font-bold text-blue-800 w-16">#</th>
                <th class="py-3 text-center font-bold text-blue-800">
                  品名規格
                </th>
                <th class="py-3 text-center font-bold text-blue-800 w-32">
                  數量
                </th>
                <th class="py-3 text-center font-bold text-blue-800 w-24">
                  單位
                </th>
                <th class="py-3 text-center font-bold text-blue-800 w-32">
                  單價
                </th>
                <th class="py-3 text-center font-bold text-blue-800 w-32">
                  複價
                </th>
                <th class="py-3 text-center font-bold text-blue-800 w-32">
                  功能
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, idx) in items"
                :key="item.id"
                :class="[
                  'transition-colors duration-150',
                  idx % 2 === 0 ? 'bg-white' : 'bg-blue-50',
                  'hover:bg-blue-100',
                ]"
              >
                <td class="text-center font-semibold text-gray-700">
                  {{ idx + 1 }}
                </td>
                <td
                  v-if="editItemId !== item.id"
                  class="text-center text-gray-800"
                >
                  {{ item.name }}
                </td>
                <td v-else class="text-center px-2">
                  <input
                    v-model="editItemForm.name"
                    class="w-full px-2 py-1 border border-blue-300 rounded focus:ring-2 focus:ring-blue-400"
                  />
                </td>
                <td
                  v-if="editItemId !== item.id"
                  class="text-center text-gray-800"
                >
                  {{ item.quantity }}
                </td>
                <td v-else class="text-center px-2">
                  <input
                    v-model.number="editItemForm.quantity"
                    type="number"
                    min="1"
                    class="w-full px-2 py-1 border border-blue-300 rounded focus:ring-2 focus:ring-blue-400"
                  />
                </td>
                <td
                  v-if="editItemId !== item.id"
                  class="text-center text-gray-800"
                >
                  {{ item.unit }}
                </td>
                <td v-else class="text-center px-2">
                  <input
                    v-model="editItemForm.unit"
                    class="w-full px-2 py-1 border border-blue-300 rounded focus:ring-2 focus:ring-blue-400"
                  />
                </td>
                <td
                  v-if="editItemId !== item.id"
                  class="text-center text-gray-800"
                >
                  {{ item.price.toLocaleString() }}
                </td>
                <td v-else class="text-center px-2">
                  <input
                    v-model.number="editItemForm.price"
                    type="number"
                    min="0"
                    class="w-full px-2 py-1 border border-blue-300 rounded focus:ring-2 focus:ring-blue-400"
                  />
                </td>
                <td class="text-center text-blue-700 font-semibold">
                  {{ (item.quantity * item.price).toLocaleString() }}
                </td>
                <td class="text-center">
                  <template v-if="editItemId !== item.id">
                    <div class="flex flex-col m-2 sm:flex-row gap-2">
                      <button
                        @click="startEditItem(item)"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex justify-center items-center"
                        title="編輯"
                      >
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <button
                        @click="removeItem(item.id)"
                        class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex justify-center items-center"
                        title="刪除"
                      >
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </template>
                  <template v-else>
                    <div class="flex flex-col m-2 sm:flex-row gap-2">
                      <button
                        @click="saveEditItem"
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex justify-center items-center"
                        title="儲存"
                      >
                        <i class="fa-solid fa-floppy-disk"></i>
                      </button>
                      <button
                        @click="cancelEditItem"
                        class="flex-1 px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition flex justify-center items-center"
                        title="取消"
                      >
                        <i class="fa-solid fa-xmark"></i>
                      </button>
                    </div>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- 自定義模板 -->
    <div
      v-if="activeTab === 'templates'"
      class="transition-opacity duration-300"
    >
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- 左側模板列表 -->
        <div class="md:col-span-1 app-card p-6">
          <h3 class="text-2xl font-bold mb-4 text-blue-700">模板列表</h3>
          <button
            @click="createNewTemplate"
            class="w-full mb-4 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg shadow transition font-semibold flex items-center justify-center"
          >
            <i class="fa-solid fa-plus mr-2"></i>新增模板
          </button>
          <LoadingPanel v-if="loading" variant="skeleton" />
          <div v-else-if="templates.length === 0" class="empty-state">
            查無項目資料
          </div>
          <ul v-else class="space-y-2">
            <li v-for="template in templates" :key="template.id">
              <a
                @click.prevent="selectTemplate(template)"
                href="#"
                :class="[
                  'block w-full text-left px-4 py-3 rounded-lg transition-all duration-200',
                  activeTemplate && activeTemplate.id === template.id
                    ? 'bg-blue-600 text-white shadow-lg'
                    : 'bg-gray-100 hover:bg-blue-100 hover:text-blue-700',
                ]"
              >
                {{ template.name || "未命名模板" }}
              </a>
            </li>
          </ul>
        </div>
        <!-- 右側模板編輯器 -->
        <div class="md:col-span-2 app-card p-8">
          <div v-if="!activeTemplate">
            <div class="text-center py-20 text-gray-500">
              <i class="fa-solid fa-puzzle-piece text-5xl mb-4"></i>
              <h3 class="text-2xl font-semibold">請選擇一個模板進行編輯</h3>
              <p class="mt-2">或點擊左側「新增模板」來建立一個新範本。</p>
            </div>
          </div>
          <div v-else>
            <h3 class="text-2xl font-bold mb-6 text-gray-800">
              {{ isCreatingNew ? "新增模板" : "編輯模板" }}
            </h3>
            <form @submit.prevent="saveTemplate">
              <!-- 模板名稱 -->
              <div class="mb-6">
                <label class="block mb-2 text-gray-700 font-medium"
                  >模板名稱</label
                >
                <input
                  v-model="activeTemplate.name"
                  type="text"
                  class="w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-800 focus:border-blue-400 transition-all duration-200"
                  placeholder="請輸入模板名稱"
                  required
                />
              </div>
              <!-- 動態欄位 -->
              <h4 class="text-xl font-bold mb-4 text-gray-700">自定義欄位</h4>
              <div class="space-y-4 mb-6">
                <div
                  v-for="(field, index) in activeTemplate.fields"
                  :key="field.id"
                  class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border"
                >
                  <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block mb-1 text-sm text-gray-600"
                        >欄位名稱</label
                      >
                      <input
                        v-model="field.label"
                        type="text"
                        class="w-full px-3 py-2 rounded-md border border-gray-300"
                        placeholder="例如：CPU"
                        required
                      />
                    </div>
                    <div>
                      <label class="block mb-1 text-sm text-gray-600"
                        >預設值</label
                      >
                      <input
                        v-model="field.value"
                        type="text"
                        class="w-full px-3 py-2 rounded-md border border-gray-300"
                        placeholder="例如：Intel i7"
                        required
                      />
                    </div>
                  </div>
                  <button
                    @click.prevent="removeField(field.id)"
                    class="flex-shrink-0 px-3 py-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                    title="刪除欄位"
                  >
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </div>
                <div
                  v-if="activeTemplate.fields.length === 0"
                  class="text-center text-gray-400 py-4 border-2 border-dashed rounded-lg"
                >
                  尚無自定義欄位
                </div>
              </div>
              <button
                @click.prevent="addField"
                class="mb-8 w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-sm transition font-semibold flex items-center justify-center"
              >
                <i class="fa-solid fa-plus mr-2"></i>新增欄位
              </button>
              <!-- 操作按鈕 -->
              <div class="flex justify-between items-center gap-4">
                <button
                  v-if="!isCreatingNew"
                  @click.prevent="deleteActiveTemplate"
                  type="button"
                  class="text-red-500 hover:text-red-700 font-semibold transition"
                >
                  <i class="fa-solid fa-trash-can mr-2"></i>刪除模板
                </button>
                <div class="flex-grow"></div>
                <div class="flex gap-4">
                  <button
                    @click.prevent="cancelEdit"
                    type="button"
                    class="px-8 py-3 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition font-semibold"
                  >
                    取消
                  </button>
                  <button
                    type="submit"
                    class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
                  >
                    <i class="fa-solid fa-floppy-disk mr-2"></i>儲存模板
                  </button>
                </div>
              </div>
            </form>
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
    <!-- 錯誤提示 -->
    <div
      v-if="error"
      class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
    >
      <div class="flex items-center justify-between">
        <div>
          <i class="fa-solid fa-exclamation-circle mr-2"></i>{{ error }}
        </div>
        <button
          @click="error = null"
          class="ml-4 text-white hover:text-gray-200"
        >
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

import LoadingPanel from "@/components/LoadingPanel.vue";

import {
  getItems,
  createItem,
  updateItem,
  deleteItem,
  getTemplates,
  createTemplate,
  updateTemplate,
  deleteTemplate,
} from "@/modules/quote/api/quoteApi";

// Tab 控制

const activeTab = ref("items");

// ========== 一般項目 ========== //

const itemForm = ref({ name: "", quantity: 1, unit: "", price: 0 });

const items = ref([]);

const editItemId = ref(null);

const editItemForm = ref({});

// ========== 自定義模板 ========== //

const templates = ref([]);

const activeTemplate = ref(null);

const isCreatingNew = ref(false);

// ========== 通用 ========== //

const showSuccess = ref(false);

const successMessage = ref("");

const loadingCount = ref(0);

const error = ref(null);

// 計算屬性：只要有任何請求在進行中就顯示載入動畫

const loading = computed(() => loadingCount.value > 0);

// 載入資料

onMounted(() => {
  loadItems();

  loadTemplates();
});

async function loadItems() {
  try {
    loadingCount.value++;
    error.value = null;
    const response = await getItems();
    items.value = response.data || response;
  } catch (err) {
    error.value = "載入項目失敗：" + err.message;
    console.error("載入項目失敗:", err);
  } finally {
    loadingCount.value--;
  }
}

async function loadTemplates() {
  try {
    loadingCount.value++;
    error.value = null;
    const response = await getTemplates();
    templates.value = response.data || response;
  } catch (err) {
    error.value = "載入模板失敗：" + err.message;
    console.error("載入模板失敗:", err);
  } finally {
    loadingCount.value--;
  }
}

// ========== 一般項目 CRUD ========== //

async function addItem() {
  try {
    loadingCount.value++;
    await createItem(itemForm.value);
    await loadItems();
    itemForm.value = { name: "", quantity: 1, unit: "", price: 0 };
    showSuccessMessage("項目已新增！");
  } catch (err) {
    error.value = "新增項目失敗：" + err.message;
    console.error("新增項目失敗:", err);
  } finally {
    loadingCount.value--;
  }
}

function startEditItem(item) {
  editItemId.value = item.id;

  editItemForm.value = { ...item };
}

async function saveEditItem() {
  try {
    loadingCount.value++;
    await updateItem(editItemId.value, editItemForm.value);
    await loadItems();
    editItemId.value = null;
    showSuccessMessage("項目已更新！");
  } catch (err) {
    error.value = "更新項目失敗：" + err.message;
    console.error("更新項目失敗:", err);
  } finally {
    loadingCount.value--;
  }
}

function cancelEditItem() {
  editItemId.value = null;
}

async function removeItem(id) {
  if (confirm("確定要刪除此項目嗎？")) {
    try {
      loadingCount.value++;
      await deleteItem(id);
      await loadItems();
      showSuccessMessage("項目已刪除！");
    } catch (err) {
      error.value = "刪除項目失敗：" + err.message;
      console.error("刪除項目失敗:", err);
    } finally {
      loadingCount.value--;
    }
  }
}

// ========== 自定義模板 CRUD ========== //

function selectTemplate(template) {
  isCreatingNew.value = false;

  // 轉換欄位格式：後端格式 (field_label, field_value) → 前端格式 (label, value)

  const convertedFields = template.fields
    ? template.fields.map((field) => ({
        id: field.id,
        label: field.field_label || field.label || "",
        value: field.field_value || field.value || "",
      }))
    : [];

  // 使用 deep copy 避免直接修改原物件

  activeTemplate.value = {
    ...JSON.parse(JSON.stringify(template)),
    fields: convertedFields,
  };
}

function createNewTemplate() {
  isCreatingNew.value = true;

  activeTemplate.value = {
    name: "",
    fields: [], // 新增時也給予一個唯一的id，方便新增field時key的管理id: `new-${crypto.randomUUID()}`,
  };
}

function addField() {
  if (!activeTemplate.value) return;

  activeTemplate.value.fields.push({
    id: crypto.randomUUID(),
    label: "",
    value: "",
  });
}

function removeField(fieldId) {
  if (!activeTemplate.value) return;

  const index = activeTemplate.value.fields.findIndex((f) => f.id === fieldId);

  if (index > -1) {
    activeTemplate.value.fields.splice(index, 1);
  }
}

async function saveTemplate() {
  if (!activeTemplate.value) return;

  try {
    loadingCount.value++;

    const convertedFields = activeTemplate.value.fields.map((field) => ({
      field_key: field.label.toLowerCase().replace(/\s+/g, "_"),
      field_label: field.label,
      field_type: "text",
      field_value: field.value,
      field_options: null,
      is_required: false,
    }));

    const templateData = {
      name: activeTemplate.value.name,
      fields: convertedFields,
    };

    if (isCreatingNew.value) {
      await createTemplate(templateData);
      showSuccessMessage("??????");
    } else {
      await updateTemplate(activeTemplate.value.id, templateData);
      showSuccessMessage("??????");
    }

    await loadTemplates();
    activeTemplate.value = null;
    isCreatingNew.value = false;
  } catch (err) {
    error.value = "???????" + err.message;
    console.error("???????", err);
  } finally {
    loadingCount.value--;
  }
}

async function deleteActiveTemplate() {
  if (!activeTemplate.value || isCreatingNew.value) return;

  if (confirm(`確定要刪除模板「${activeTemplate.value.name}」嗎？`)) {
    try {
      loadingCount.value++;
      await deleteTemplate(activeTemplate.value.id);
      showSuccessMessage("模板已刪除！");
      await loadTemplates();
      activeTemplate.value = null;
    } catch (err) {
      error.value = "刪除模板失敗：" + err.message;
      console.error("刪除模板失敗:", err);
    } finally {
      loadingCount.value--;
    }
  }
}

function cancelEdit() {
  activeTemplate.value = null;

  isCreatingNew.value = false;
}

// ========== 成功提示 ========== //

function showSuccessMessage(message) {
  successMessage.value = message;

  showSuccess.value = true;

  setTimeout(() => {
    showSuccess.value = false;
  }, 2000);
}
</script>
