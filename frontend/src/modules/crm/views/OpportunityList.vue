<template>
  <div class="opportunity-list">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">商機列表</h1>
        <p class="text-sm text-gray-500 mt-1">查看目前 CRM 商機進度與金額分布</p>
      </div>

      <button
        @click="goCustomers"
        class="px-4 py-2 border rounded-lg hover:bg-gray-50"
      >
        前往客戶列表
      </button>
    </div>

    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋商機或客戶名稱"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
          @input="handleSearchInput"
        />

        <select
          v-model="filters.status"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
          @change="handleFilterChange"
        >
          <option value="">全部狀態</option>
          <option value="open">進行中</option>
          <option value="won">已成交</option>
          <option value="lost">已失敗</option>
        </select>

        <select
          v-model="filters.stage"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
          @change="handleFilterChange"
        >
          <option value="">全部階段</option>
          <option value="new">新建</option>
          <option value="qualified">已確認</option>
          <option value="proposal">提案中</option>
          <option value="negotiation">談判中</option>
          <option value="won">已成交</option>
          <option value="lost">已失敗</option>
        </select>

        <input
          v-model.trim="filters.customer_id"
          type="number"
          min="1"
          placeholder="客戶 ID"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
          @input="handleSearchInput"
        />

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

      <div v-else-if="opportunities.length === 0" class="empty-state">
        <i class="fa-solid fa-chart-line text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有商機資料</h3>
        <p class="text-sm text-gray-500">可先建立商機資料，或調整篩選條件後再試一次。</p>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">商機名稱</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">客戶</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">階段</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">金額</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">預計結案</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">備註</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="opportunity in opportunities" :key="opportunity.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm text-gray-900">
                  <div class="font-medium">{{ opportunity.name || `#${opportunity.id}` }}</div>
                  <div class="text-xs text-gray-500 mt-1">ID: {{ opportunity.id }}</div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  <button
                    v-if="opportunity.customer_id"
                    @click="goCustomerDetail(opportunity.customer_id)"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    {{ opportunity.customer_name || `客戶 #${opportunity.customer_id}` }}
                  </button>
                  <template v-else>-</template>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ getStageLabel(opportunity.stage) }}</td>
                <td class="px-4 py-4 text-sm">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusClass(opportunity.status)"
                  >
                    {{ getStatusLabel(opportunity.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-right text-gray-700">{{ formatCurrency(opportunity.amount) }}</td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ formatDate(opportunity.expected_close_date) }}</td>
                <td class="px-4 py-4 text-sm text-gray-700">{{ truncate(opportunity.notes) }}</td>
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
import { useRoute, useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useOpportunity } from '../composables/useOpportunity'

const route = useRoute()
const router = useRouter()
const { opportunities, pagination, loading, fetchOpportunities } = useOpportunity()

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  status: '',
  stage: '',
  customer_id: ''
})

let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    status: filters.status || undefined,
    stage: filters.stage || undefined,
    customer_id: filters.customer_id || undefined,
    sort_by: 'updated_at',
    sort_order: 'desc'
  }
}

async function loadOpportunities() {
  await fetchOpportunities(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadOpportunities()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadOpportunities()
}

async function resetFilters() {
  filters.page = 1
  filters.search = ''
  filters.status = ''
  filters.stage = ''
  filters.customer_id = ''
  await loadOpportunities()
}

async function goPage(page) {
  if (page < 1 || page > pagination.value.lastPage || loading.value) return
  filters.page = page
  await loadOpportunities()
}

function goCustomers() {
  router.push('/crm/customers')
}

function goCustomerDetail(customerId) {
  router.push(`/crm/customers/${customerId}`)
}

function getStageLabel(stage) {
  const map = {
    new: '新建',
    qualified: '已確認',
    proposal: '提案中',
    negotiation: '談判中',
    won: '已成交',
    lost: '已失敗'
  }

  return map[stage] || stage || '-'
}

function getStatusLabel(status) {
  const map = {
    open: '進行中',
    won: '已成交',
    lost: '已失敗'
  }

  return map[status] || status || '-'
}

function getStatusClass(status) {
  if (status === 'won') return 'bg-emerald-100 text-emerald-700'
  if (status === 'lost') return 'bg-red-100 text-red-700'
  return 'bg-blue-100 text-blue-700'
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

function truncate(value, maxLength = 40) {
  if (!value) return '-'
  if (value.length <= maxLength) return value
  return `${value.slice(0, maxLength)}...`
}

onMounted(async () => {
  filters.customer_id = route.query.customer_id ? String(route.query.customer_id) : ''
  await loadOpportunities()
})
</script>
