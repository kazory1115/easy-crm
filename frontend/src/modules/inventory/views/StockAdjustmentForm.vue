<template>
  <div class="stock-adjustment-form">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">庫存調整</h1>
        <p class="text-sm text-gray-500 mt-1">針對單一倉庫品項做盤點修正，並查看最近調整紀錄</p>
      </div>

      <button
        @click="goMovementList"
        class="px-4 py-2 border rounded-lg hover:bg-gray-50"
      >
        返回異動列表
      </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-1">
        <div class="app-card p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-700">建立調整</h2>

          <div>
            <label class="block text-sm text-gray-600 mb-1">倉庫 *</label>
            <select
              v-model.number="form.warehouse_id"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
            >
              <option :value="0">請選擇倉庫</option>
              <option
                v-for="warehouse in warehouseOptions"
                :key="warehouse.id"
                :value="warehouse.id"
              >
                {{ warehouse.name }} ({{ warehouse.code }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">品項 *</label>
            <select
              v-model.number="form.item_id"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
            >
              <option :value="0">請選擇品項</option>
              <option
                v-for="item in itemOptions"
                :key="item.id"
                :value="item.id"
              >
                {{ item.name }}{{ item.unit ? ` (${item.unit})` : '' }}
              </option>
            </select>
          </div>

          <div class="rounded-lg bg-amber-50 p-4 text-sm">
            <div class="text-gray-600 mb-1">目前庫存</div>
            <div class="text-xl font-bold text-gray-900">
              {{ currentStockPreview ? currentStockPreview.quantity : 0 }}
            </div>
            <div class="text-xs text-gray-500 mt-2">
              可用 {{ currentStockPreview ? currentStockPreview.available_quantity : 0 }} /
              預留 {{ currentStockPreview ? currentStockPreview.reserved : 0 }}
            </div>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">調整後庫存 *</label>
            <input
              v-model.number="form.after_qty"
              type="number"
              min="0"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">調整原因</label>
            <select
              v-model="form.reason"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
            >
              <option
                v-for="option in ADJUSTMENT_REASON_OPTIONS"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">備註</label>
            <textarea
              v-model.trim="form.note"
              rows="3"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
              placeholder="例如：月底盤點、損壞報廢、資料修正"
            ></textarea>
          </div>

          <p v-if="formError" class="text-sm text-red-600">{{ formError }}</p>

          <button
            @click="submitAdjustment"
            class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-60 disabled:cursor-not-allowed"
            :disabled="submitting || optionsLoading"
          >
            {{ submitting ? '送出中...' : '提交庫存調整' }}
          </button>
        </div>
      </div>

      <div class="xl:col-span-2">
        <div class="app-card p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input
              v-model.trim="filters.search"
              type="text"
              placeholder="搜尋調整原因、備註、品項"
              class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
              @input="handleSearchInput"
            />

            <select
              v-model.number="filters.warehouse_id"
              class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
              @change="handleFilterChange"
            >
              <option :value="0">全部倉庫</option>
              <option
                v-for="warehouse in warehouseOptions"
                :key="warehouse.id"
                :value="warehouse.id"
              >
                {{ warehouse.name }}
              </option>
            </select>

            <select
              v-model="filters.reason"
              class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
              @change="handleFilterChange"
            >
              <option value="">全部原因</option>
              <option
                v-for="option in ADJUSTMENT_REASON_OPTIONS"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>

            <button
              @click="resetFilters"
              class="px-4 py-2 border rounded-lg hover:bg-gray-50"
            >
              重置篩選
            </button>
          </div>
        </div>

        <div class="app-card overflow-hidden">
          <LoadingPanel v-if="adjustmentLoading" variant="table" />

          <div v-else-if="stockAdjustments.length === 0" class="empty-state">
            <i class="fa-solid fa-sliders text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有調整紀錄</h3>
            <p class="text-sm text-gray-500">送出第一筆盤點修正後，會顯示在這裡。</p>
          </div>

          <div v-else>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">時間</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">倉庫</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">品項</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">調整前</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">調整後</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">差異</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">原因</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">備註</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="adjustment in stockAdjustments" :key="adjustment.id" class="hover:bg-gray-50">
                    <td class="px-4 py-4 text-sm text-gray-700">{{ formatDateTime(adjustment.created_at) }}</td>
                    <td class="px-4 py-4 text-sm text-gray-700">
                      <div>{{ adjustment.warehouse_name || `倉庫 #${adjustment.warehouse_id}` }}</div>
                      <div class="text-xs text-gray-500 mt-1">{{ adjustment.warehouse_code || '-' }}</div>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900">
                      {{ adjustment.item_name || `品項 #${adjustment.item_id}` }}
                    </td>
                    <td class="px-4 py-4 text-sm text-right text-gray-700">{{ adjustment.before_qty }}</td>
                    <td class="px-4 py-4 text-sm text-right text-gray-700">{{ adjustment.after_qty }}</td>
                    <td class="px-4 py-4 text-sm text-right font-semibold" :class="adjustment.difference < 0 ? 'text-red-700' : 'text-emerald-700'">
                      {{ adjustment.difference }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700">{{ getAdjustmentReasonLabel(adjustment.reason) }}</td>
                    <td class="px-4 py-4 text-sm text-gray-700">{{ truncate(adjustment.note) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div
              v-if="adjustmentPagination.total > 0"
              class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3"
            >
              <div class="text-sm text-gray-600">
                共 {{ adjustmentPagination.total }} 筆，第 {{ adjustmentPagination.currentPage }} / {{ adjustmentPagination.lastPage }} 頁
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="goPage(adjustmentPagination.currentPage - 1)"
                  class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="adjustmentPagination.currentPage <= 1 || adjustmentLoading"
                >
                  上一頁
                </button>
                <button
                  @click="goPage(adjustmentPagination.currentPage + 1)"
                  class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
                  :disabled="adjustmentPagination.currentPage >= adjustmentPagination.lastPage || adjustmentLoading"
                >
                  下一頁
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useInventory } from '../composables/useInventory'
import { ADJUSTMENT_REASON_OPTIONS, getAdjustmentReasonLabel } from '../constants'

const router = useRouter()
const {
  stockAdjustments,
  warehouseOptions,
  itemOptions,
  adjustmentPagination,
  adjustmentLoading,
  optionsLoading,
  submitting,
  fetchStockAdjustments,
  createStockAdjustment,
  fetchWarehouseOptions,
  fetchItemOptions,
  fetchStockPreview
} = useInventory()

const currentStockPreview = ref(null)
const formError = ref('')

const form = reactive({
  warehouse_id: 0,
  item_id: 0,
  after_qty: 0,
  reason: 'count',
  note: ''
})

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  warehouse_id: 0,
  reason: ''
})

let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    warehouse_id: filters.warehouse_id || undefined,
    reason: filters.reason || undefined,
    sort_by: 'created_at',
    sort_order: 'desc'
  }
}

async function loadAdjustments() {
  await fetchStockAdjustments(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadAdjustments()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadAdjustments()
}

async function resetFilters() {
  filters.page = 1
  filters.search = ''
  filters.warehouse_id = 0
  filters.reason = ''
  await loadAdjustments()
}

async function goPage(page) {
  if (page < 1 || page > adjustmentPagination.value.lastPage || adjustmentLoading.value) return
  filters.page = page
  await loadAdjustments()
}

function resetForm() {
  form.warehouse_id = 0
  form.item_id = 0
  form.after_qty = 0
  form.reason = 'count'
  form.note = ''
  currentStockPreview.value = null
  formError.value = ''
}

function validatePayload(payload) {
  if (!payload.warehouse_id) return '請選擇倉庫'
  if (!payload.item_id) return '請選擇品項'
  if (payload.after_qty < 0) return '調整後庫存不可小於 0'
  return ''
}

async function submitAdjustment() {
  formError.value = ''

  const payload = {
    warehouse_id: Number(form.warehouse_id),
    item_id: Number(form.item_id),
    after_qty: Number(form.after_qty),
    reason: form.reason || null,
    note: form.note || null
  }

  const validationMessage = validatePayload(payload)
  if (validationMessage) {
    formError.value = validationMessage
    return
  }

  const result = await createStockAdjustment(payload)
  if (!result?.adjustment?.id) {
    formError.value = '庫存調整送出失敗，請檢查欄位後重試'
    return
  }

  resetForm()
  await loadAdjustments()
}

function goMovementList() {
  router.push('/inventory/movements')
}

function formatDateTime(value) {
  if (!value) return '-'
  return new Date(value).toLocaleString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function truncate(value, maxLength = 36) {
  if (!value) return '-'
  if (value.length <= maxLength) return value
  return `${value.slice(0, maxLength)}...`
}

watch(
  () => [form.warehouse_id, form.item_id],
  async ([warehouseId, itemId]) => {
    if (!warehouseId || !itemId) {
      currentStockPreview.value = null
      return
    }

    currentStockPreview.value = await fetchStockPreview({
      warehouse_id: Number(warehouseId),
      item_id: Number(itemId)
    })
  }
)

onMounted(async () => {
  await Promise.all([
    fetchWarehouseOptions(),
    fetchItemOptions(),
    loadAdjustments()
  ])
})
</script>
