<template>
  <div class="warehouse-list">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">倉庫列表</h1>
        <p class="text-sm text-gray-500 mt-1">查看倉庫狀態、位置與庫存相關統計</p>
      </div>

      <button
        @click="goStockList"
        class="px-4 py-2 border rounded-lg hover:bg-gray-50"
      >
        前往庫存列表
      </button>
    </div>

    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋倉庫名稱、代碼、位置"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          @input="handleSearchInput"
        />

        <select
          v-model="filters.status"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
          @change="handleFilterChange"
        >
          <option value="">全部狀態</option>
          <option value="active">啟用</option>
          <option value="inactive">停用</option>
        </select>

        <div class="px-4 py-2 rounded-lg bg-amber-50 text-sm text-amber-800 flex items-center">
          目前共 {{ warehousePagination.total }} 個倉庫
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
      <LoadingPanel v-if="warehouseLoading" variant="table" />

      <div v-else-if="warehouses.length === 0" class="empty-state">
        <i class="fa-solid fa-warehouse text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有倉庫資料</h3>
        <p class="text-sm text-gray-500">請先建立倉庫，或調整搜尋條件後再試一次。</p>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">倉庫</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">位置</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">庫存筆數</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">異動筆數</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">調整筆數</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">更新時間</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="warehouse in warehouses" :key="warehouse.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm text-gray-900">
                  <div class="font-medium">{{ warehouse.name || `#${warehouse.id}` }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ warehouse.code || '-' }}</div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ warehouse.location || '-' }}</td>
                <td class="px-4 py-4 text-sm">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getWarehouseStatusClass(warehouse.status)"
                  >
                    {{ getWarehouseStatusLabel(warehouse.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-center text-gray-700">{{ warehouse.stock_levels_count }}</td>
                <td class="px-4 py-4 text-sm text-center text-gray-700">{{ warehouse.stock_movements_count }}</td>
                <td class="px-4 py-4 text-sm text-center text-gray-700">{{ warehouse.stock_adjustments_count }}</td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ formatDateTime(warehouse.updated_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="warehousePagination.total > 0"
          class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3"
        >
          <div class="text-sm text-gray-600">
            共 {{ warehousePagination.total }} 筆，第 {{ warehousePagination.currentPage }} / {{ warehousePagination.lastPage }} 頁
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="goPage(warehousePagination.currentPage - 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="warehousePagination.currentPage <= 1 || warehouseLoading"
            >
              上一頁
            </button>
            <button
              @click="goPage(warehousePagination.currentPage + 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="warehousePagination.currentPage >= warehousePagination.lastPage || warehouseLoading"
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
import { getWarehouseStatusClass, getWarehouseStatusLabel } from '../constants'

const router = useRouter()
const { warehouses, warehousePagination, warehouseLoading, fetchWarehouses } = useInventory()

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  status: ''
})

let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    status: filters.status || undefined,
    sort_by: 'updated_at',
    sort_order: 'desc'
  }
}

async function loadWarehouses() {
  await fetchWarehouses(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadWarehouses()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadWarehouses()
}

async function resetFilters() {
  filters.page = 1
  filters.search = ''
  filters.status = ''
  await loadWarehouses()
}

async function goPage(page) {
  if (page < 1 || page > warehousePagination.value.lastPage || warehouseLoading.value) return
  filters.page = page
  await loadWarehouses()
}

function goStockList() {
  router.push('/inventory/stock')
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
  await loadWarehouses()
})
</script>
