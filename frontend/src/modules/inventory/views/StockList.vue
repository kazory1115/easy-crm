<template>
  <div class="stock-list">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">庫存列表</h1>
        <p class="text-sm text-gray-500 mt-1">查看各倉庫品項庫存、可用量與低庫存狀態</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="goMovementList"
          class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700"
        >
          前往庫存異動
        </button>
        <button
          @click="goAdjustmentForm"
          class="px-4 py-2 border rounded-lg hover:bg-gray-50"
        >
          庫存調整
        </button>
      </div>
    </div>

    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋倉庫或品項"
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
            {{ warehouse.name }} ({{ warehouse.code }})
          </option>
        </select>

        <select
          v-model="filters.stock_status"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          @change="handleFilterChange"
        >
          <option value="">全部庫存狀態</option>
          <option
            v-for="option in STOCK_STATUS_OPTIONS"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>

        <div class="px-4 py-2 rounded-lg bg-amber-50 text-sm text-amber-800 flex items-center">
          共 {{ stockPagination.total }} 筆庫存
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
      <LoadingPanel v-if="stockLoading" variant="table" />

      <div v-else-if="stockLevels.length === 0" class="empty-state">
        <i class="fa-solid fa-boxes-stacked text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有庫存資料</h3>
        <p class="text-sm text-gray-500">請先建立庫存異動，或調整篩選條件後再試一次。</p>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">品項</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">倉庫</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">在庫</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">預留</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">可用</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">最低庫存</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">更新時間</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="stock in stockLevels" :key="stock.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm text-gray-900">
                  <div class="font-medium">{{ stock.item_name || `品項 #${stock.item_id}` }}</div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ stock.item_category || '-' }} / {{ stock.item_unit || '-' }}
                  </div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  <div>{{ stock.warehouse_name || `倉庫 #${stock.warehouse_id}` }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ stock.warehouse_code || '-' }}</div>
                </td>
                <td class="px-4 py-4 text-sm text-right text-gray-700">{{ stock.quantity }}</td>
                <td class="px-4 py-4 text-sm text-right text-gray-700">{{ stock.reserved }}</td>
                <td class="px-4 py-4 text-sm text-right font-semibold text-gray-900">{{ stock.available_quantity }}</td>
                <td class="px-4 py-4 text-sm text-right text-gray-700">{{ stock.min_level }}</td>
                <td class="px-4 py-4 text-sm">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStockHealthClass(stock.stock_status)"
                  >
                    {{ getStockHealthLabel(stock.stock_status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ formatDateTime(stock.updated_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="stockPagination.total > 0"
          class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3"
        >
          <div class="text-sm text-gray-600">
            共 {{ stockPagination.total }} 筆，第 {{ stockPagination.currentPage }} / {{ stockPagination.lastPage }} 頁
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="goPage(stockPagination.currentPage - 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="stockPagination.currentPage <= 1 || stockLoading"
            >
              上一頁
            </button>
            <button
              @click="goPage(stockPagination.currentPage + 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="stockPagination.currentPage >= stockPagination.lastPage || stockLoading"
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
import { onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useInventory } from '../composables/useInventory'
import { STOCK_STATUS_OPTIONS, getStockHealthClass, getStockHealthLabel } from '../constants'

const router = useRouter()
const {
  stockLevels,
  warehouseOptions,
  stockPagination,
  stockLoading,
  fetchStockLevels,
  fetchWarehouseOptions
} = useInventory()

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  warehouse_id: 0,
  stock_status: ''
})

let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    warehouse_id: filters.warehouse_id || undefined,
    stock_status: filters.stock_status || undefined,
    sort_by: 'updated_at',
    sort_order: 'desc'
  }
}

async function loadStocks() {
  await fetchStockLevels(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadStocks()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadStocks()
}

async function resetFilters() {
  filters.page = 1
  filters.search = ''
  filters.warehouse_id = 0
  filters.stock_status = ''
  await loadStocks()
}

async function goPage(page) {
  if (page < 1 || page > stockPagination.value.lastPage || stockLoading.value) return
  filters.page = page
  await loadStocks()
}

function goMovementList() {
  router.push('/inventory/movements')
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

onMounted(async () => {
  await Promise.all([
    fetchWarehouseOptions(),
    loadStocks()
  ])
})
</script>
