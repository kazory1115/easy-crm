<template>
  <div class="order-list">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">訂單列表</h1>
        <p class="text-sm text-gray-500 mt-1">查看、篩選並管理訂單資料</p>
      </div>

      <button
        @click="goCreate"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        新增訂單
      </button>
    </div>

    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋單號、客戶、專案"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @input="handleSearchInput"
        />

        <select
          v-model="filters.status"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="handleFilterChange"
        >
          <option value="">全部訂單狀態</option>
          <option
            v-for="status in ORDER_STATUS_OPTIONS"
            :key="status.value"
            :value="status.value"
          >
            {{ status.label }}
          </option>
        </select>

        <select
          v-model="filters.payment_status"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="handleFilterChange"
        >
          <option value="">全部付款狀態</option>
          <option
            v-for="status in PAYMENT_STATUS_OPTIONS"
            :key="status.value"
            :value="status.value"
          >
            {{ status.label }}
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
      <LoadingPanel v-if="loading" variant="table" />

      <div v-else-if="orders.length === 0" class="empty-state">
        <i class="fa-solid fa-inbox text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有訂單</h3>
        <p class="text-sm text-gray-500 mb-5">可以先建立第一筆訂單，或調整篩選條件後再試一次。</p>
        <button
          @click="goCreate"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          建立第一筆訂單
        </button>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">訂單編號</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">客戶名稱</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">專案名稱</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">訂單日期</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">付款狀態</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">總金額</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm font-medium text-gray-900">
                  {{ order.order_number || `#${order.id}` }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  {{ order.customer_name || '-' }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  {{ order.project_name || '-' }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  {{ formatDate(order.order_date) }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getOrderStatusClass(order.status)"
                  >
                    {{ getOrderStatusLabel(order.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getPaymentStatusClass(order.payment_status)"
                  >
                    {{ getPaymentStatusLabel(order.payment_status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-right font-semibold text-gray-800">
                  {{ formatCurrency(order.total_amount) }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <div class="flex items-center gap-3">
                    <button
                      @click="goDetail(order.id)"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      查看
                    </button>
                    <button
                      @click="goEdit(order.id)"
                      class="text-emerald-600 hover:text-emerald-800"
                    >
                      編輯
                    </button>
                    <button
                      @click="handleDelete(order)"
                      class="text-red-600 hover:text-red-800"
                      :disabled="submitting"
                    >
                      刪除
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="pagination.total > 0"
          class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3"
        >
          <div class="text-sm text-gray-600">
            共 {{ pagination.total }} 筆，第 {{ pagination.currentPage }} / {{ pagination.lastPage }} 頁
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="goPage(pagination.currentPage - 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="pagination.currentPage <= 1 || loading"
            >
              上一頁
            </button>
            <button
              @click="goPage(pagination.currentPage + 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="pagination.currentPage >= pagination.lastPage || loading"
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
import { useOrder } from '../composables/useOrder'
import {
  ORDER_STATUS_OPTIONS,
  PAYMENT_STATUS_OPTIONS,
  getOrderStatusClass,
  getOrderStatusLabel,
  getPaymentStatusClass,
  getPaymentStatusLabel
} from '../constants'

const router = useRouter()
const {
  orders,
  pagination,
  loading,
  submitting,
  fetchOrders,
  deleteOrder
} = useOrder()

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  status: '',
  payment_status: ''
})

let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    status: filters.status || undefined,
    payment_status: filters.payment_status || undefined,
    sort_by: 'created_at',
    sort_order: 'desc'
  }
}

async function loadOrders() {
  await fetchOrders(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadOrders()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadOrders()
}

async function resetFilters() {
  filters.search = ''
  filters.status = ''
  filters.payment_status = ''
  filters.page = 1
  await loadOrders()
}

function goCreate() {
  router.push('/order/create')
}

function goDetail(id) {
  router.push(`/order/detail/${id}`)
}

function goEdit(id) {
  router.push(`/order/edit/${id}`)
}

async function goPage(page) {
  if (page < 1 || page > pagination.value.lastPage || loading.value) return
  filters.page = page
  await loadOrders()
}

async function handleDelete(order) {
  const orderNumber = order.order_number || `#${order.id}`
  const confirmed = window.confirm(`確定要刪除訂單 ${orderNumber} 嗎？`)
  if (!confirmed) return

  const success = await deleteOrder(order.id)
  if (!success) return

  if (orders.value.length === 1 && filters.page > 1) {
    filters.page -= 1
  }
  await loadOrders()
}

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

function formatCurrency(value) {
  return `${Number(value || 0).toLocaleString()} 元`
}

onMounted(async () => {
  await loadOrders()
})
</script>
