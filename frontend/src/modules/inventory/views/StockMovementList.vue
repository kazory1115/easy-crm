<template>
  <div class="stock-movement-list">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">庫存異動</h1>
        <p class="text-sm text-gray-500 mt-1">處理入庫、出庫、調撥，並查看異動歷史</p>
      </div>

      <button
        @click="goAdjustmentForm"
        class="px-4 py-2 border rounded-lg hover:bg-gray-50"
      >
        前往庫存調整
      </button>
    </div>

    <div class="app-card p-6 mb-6 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-700">新增庫存異動</h2>
        <span class="text-sm text-gray-500">MVP 僅支援入庫 / 出庫 / 調撥</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">異動類型 *</label>
          <select
            v-model="movementForm.type"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          >
            <option
              v-for="option in MOVEMENT_TYPE_OPTIONS"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">品項 *</label>
          <select
            v-model.number="movementForm.item_id"
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

        <div v-if="movementForm.type !== 'transfer'">
          <label class="block text-sm text-gray-600 mb-1">倉庫 *</label>
          <select
            v-model.number="movementForm.warehouse_id"
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

        <div v-if="movementForm.type === 'transfer'">
          <label class="block text-sm text-gray-600 mb-1">來源倉庫 *</label>
          <select
            v-model.number="movementForm.source_warehouse_id"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          >
            <option :value="0">請選擇來源倉庫</option>
            <option
              v-for="warehouse in warehouseOptions"
              :key="warehouse.id"
              :value="warehouse.id"
            >
              {{ warehouse.name }} ({{ warehouse.code }})
            </option>
          </select>
        </div>

        <div v-if="movementForm.type === 'transfer'">
          <label class="block text-sm text-gray-600 mb-1">目標倉庫 *</label>
          <select
            v-model.number="movementForm.target_warehouse_id"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          >
            <option :value="0">請選擇目標倉庫</option>
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
          <label class="block text-sm text-gray-600 mb-1">數量 *</label>
          <input
            v-model.number="movementForm.quantity"
            type="number"
            min="1"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          />
        </div>

        <div class="md:col-span-2 xl:col-span-4">
          <label class="block text-sm text-gray-600 mb-1">備註</label>
          <textarea
            v-model.trim="movementForm.note"
            rows="2"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
            placeholder="例如：採購入庫、手動出庫、門市補貨調撥"
          ></textarea>
        </div>
      </div>

      <p v-if="formError" class="text-sm text-red-600">{{ formError }}</p>

      <div class="flex justify-end">
        <button
          @click="submitMovement"
          class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="submitting || optionsLoading"
        >
          {{ submitting ? '送出中...' : '提交異動' }}
        </button>
      </div>
    </div>

    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋異動備註、倉庫、品項"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          @input="handleSearchInput"
        />

        <select
          v-model="filters.type"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          @change="handleFilterChange"
        >
          <option value="">全部異動類型</option>
          <option
            v-for="option in MOVEMENT_TYPE_OPTIONS"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>

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

        <div class="px-4 py-2 rounded-lg bg-amber-50 text-sm text-amber-800 flex items-center">
          共 {{ movementPagination.total }} 筆異動
        </div>

        <button
          @click="resetFilters"
          class="px-4 py-2 border rounded-lg hover:bg-gray-50"
        >
          重置篩選
        </button>
      </div>
    </div>

    <div class="app-card overflow-hidden">
      <LoadingPanel v-if="movementLoading" variant="table" />

      <div v-else-if="stockMovements.length === 0" class="empty-state">
        <i class="fa-solid fa-right-left text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有庫存異動</h3>
        <p class="text-sm text-gray-500">送出第一筆入庫、出庫或調撥後，會顯示在這裡。</p>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">時間</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">類型</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">品項</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">倉庫</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">數量</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">備註</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">建立者</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="movement in stockMovements" :key="movement.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm text-gray-700">{{ formatDateTime(movement.occurred_at) }}</td>
                <td class="px-4 py-4 text-sm">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getMovementTypeClass(movement.type)"
                  >
                    {{ getMovementTypeLabel(movement.type) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-900">
                  {{ movement.item_name || `品項 #${movement.item_id}` }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  <div>{{ movement.warehouse_name || `倉庫 #${movement.warehouse_id}` }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ movement.warehouse_code || '-' }}</div>
                </td>
                <td class="px-4 py-4 text-sm text-right font-semibold" :class="movement.quantity < 0 ? 'text-red-700' : 'text-emerald-700'">
                  {{ movement.quantity }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ truncate(movement.note) }}</td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ movement.creator_name || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="movementPagination.total > 0"
          class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3"
        >
          <div class="text-sm text-gray-600">
            共 {{ movementPagination.total }} 筆，第 {{ movementPagination.currentPage }} / {{ movementPagination.lastPage }} 頁
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="goPage(movementPagination.currentPage - 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="movementPagination.currentPage <= 1 || movementLoading"
            >
              上一頁
            </button>
            <button
              @click="goPage(movementPagination.currentPage + 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="movementPagination.currentPage >= movementPagination.lastPage || movementLoading"
            >
              下一頁
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useInventory } from '../composables/useInventory'
import { MOVEMENT_TYPE_OPTIONS, getMovementTypeClass, getMovementTypeLabel } from '../constants'

const router = useRouter()
const {
  stockMovements,
  warehouseOptions,
  itemOptions,
  movementPagination,
  movementLoading,
  optionsLoading,
  submitting,
  fetchStockMovements,
  createStockMovement,
  fetchWarehouseOptions,
  fetchItemOptions
} = useInventory()

const movementForm = reactive({
  type: 'inbound',
  item_id: 0,
  warehouse_id: 0,
  source_warehouse_id: 0,
  target_warehouse_id: 0,
  quantity: 1,
  note: ''
})

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  type: '',
  warehouse_id: 0
})

const formError = ref('')
let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    type: filters.type || undefined,
    warehouse_id: filters.warehouse_id || undefined,
    sort_by: 'occurred_at',
    sort_order: 'desc'
  }
}

async function loadMovements() {
  await fetchStockMovements(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadMovements()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadMovements()
}

async function resetFilters() {
  filters.page = 1
  filters.search = ''
  filters.type = ''
  filters.warehouse_id = 0
  await loadMovements()
}

async function goPage(page) {
  if (page < 1 || page > movementPagination.value.lastPage || movementLoading.value) return
  filters.page = page
  await loadMovements()
}

function resetMovementForm() {
  movementForm.type = 'inbound'
  movementForm.item_id = 0
  movementForm.warehouse_id = 0
  movementForm.source_warehouse_id = 0
  movementForm.target_warehouse_id = 0
  movementForm.quantity = 1
  movementForm.note = ''
  formError.value = ''
}

function buildMovementPayload() {
  const payload = {
    type: movementForm.type,
    item_id: Number(movementForm.item_id),
    quantity: Number(movementForm.quantity),
    note: movementForm.note || null
  }

  if (movementForm.type === 'transfer') {
    payload.source_warehouse_id = Number(movementForm.source_warehouse_id)
    payload.target_warehouse_id = Number(movementForm.target_warehouse_id)
  } else {
    payload.warehouse_id = Number(movementForm.warehouse_id)
  }

  return payload
}

function validateMovementPayload(payload) {
  if (!payload.item_id) return '請選擇品項'
  if (!payload.quantity || payload.quantity <= 0) return '數量必須大於 0'

  if (payload.type === 'transfer') {
    if (!payload.source_warehouse_id || !payload.target_warehouse_id) {
      return '調撥必須選擇來源與目標倉庫'
    }
    if (payload.source_warehouse_id === payload.target_warehouse_id) {
      return '來源倉庫與目標倉庫不可相同'
    }
    return ''
  }

  if (!payload.warehouse_id) return '請選擇倉庫'

  return ''
}

async function submitMovement() {
  formError.value = ''
  const payload = buildMovementPayload()
  const validationMessage = validateMovementPayload(payload)

  if (validationMessage) {
    formError.value = validationMessage
    return
  }

  const result = await createStockMovement(payload)
  if (!result) {
    formError.value = '庫存異動送出失敗，請檢查欄位後重試'
    return
  }

  resetMovementForm()
  await loadMovements()
}

function goAdjustmentForm() {
  router.push('/inventory/adjustments')
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

function truncate(value, maxLength = 40) {
  if (!value) return '-'
  if (value.length <= maxLength) return value
  return `${value.slice(0, maxLength)}...`
}

onMounted(async () => {
  await Promise.all([
    fetchWarehouseOptions(),
    fetchItemOptions(),
    loadMovements()
  ])
})
</script>
