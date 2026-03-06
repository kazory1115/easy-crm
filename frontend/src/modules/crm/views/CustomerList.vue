<template>
  <div class="customer-list">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">客戶列表</h1>
        <p class="text-sm text-gray-500 mt-1">查詢、篩選並管理 CRM 客戶主資料</p>
      </div>

      <button
        @click="goCreate"
        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
      >
        新增客戶
      </button>
    </div>

    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋客戶、聯絡人、電話、統編"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
          @input="handleSearchInput"
        />

        <select
          v-model="filters.status"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
          @change="handleFilterChange"
        >
          <option value="">全部狀態</option>
          <option value="active">啟用</option>
          <option value="inactive">停用</option>
        </select>

        <input
          v-model.trim="filters.industry"
          type="text"
          placeholder="產業關鍵字"
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

      <div v-else-if="customers.length === 0" class="empty-state">
        <i class="fa-solid fa-address-book text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">目前沒有客戶資料</h3>
        <p class="text-sm text-gray-500 mb-5">可以先建立第一筆客戶，或調整篩選條件後再試一次。</p>
        <button
          @click="goCreate"
          class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
        >
          建立第一筆客戶
        </button>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">客戶名稱</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">主要聯絡</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">聯絡方式</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">產業</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">CRM 資料</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">更新時間</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="customer in customers" :key="customer.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-sm">
                  <div class="font-medium text-gray-900">{{ customer.name || `#${customer.id}` }}</div>
                  <div class="text-xs text-gray-500 mt-1">ID: {{ customer.id }}</div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  {{ customer.contact_person || '-' }}
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  <div>{{ customer.phone || customer.mobile || '-' }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ customer.email || '-' }}</div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  {{ customer.industry || '-' }}
                </td>
                <td class="px-4 py-4 text-sm text-center text-gray-700">
                  <div>聯絡人 {{ customer.contacts_count }}</div>
                  <div>活動 {{ customer.activities_count }}</div>
                  <div>商機 {{ customer.opportunities_count }}</div>
                </td>
                <td class="px-4 py-4 text-sm">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusClass(customer.status)"
                  >
                    {{ getStatusLabel(customer.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-700">
                  {{ formatDateTime(customer.updated_at) }}
                </td>
                <td class="px-4 py-4 text-sm">
                  <div class="flex items-center gap-3">
                    <button
                      @click="goDetail(customer.id)"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      查看
                    </button>
                    <button
                      @click="goEdit(customer.id)"
                      class="text-emerald-600 hover:text-emerald-800"
                    >
                      編輯
                    </button>
                    <button
                      @click="handleDelete(customer)"
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
import { useCustomer } from '../composables/useCustomer'

const router = useRouter()
const {
  customers,
  pagination,
  loading,
  submitting,
  fetchCustomers,
  deleteCustomer
} = useCustomer()

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  status: '',
  industry: ''
})

let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    status: filters.status || undefined,
    industry: filters.industry || undefined,
    sort_by: 'updated_at',
    sort_order: 'desc'
  }
}

async function loadCustomers() {
  await fetchCustomers(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadCustomers()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadCustomers()
}

async function resetFilters() {
  filters.page = 1
  filters.search = ''
  filters.status = ''
  filters.industry = ''
  await loadCustomers()
}

function goCreate() {
  router.push('/crm/customers/create')
}

function goDetail(id) {
  router.push(`/crm/customers/${id}`)
}

function goEdit(id) {
  router.push(`/crm/customers/${id}/edit`)
}

async function goPage(page) {
  if (page < 1 || page > pagination.value.lastPage || loading.value) return
  filters.page = page
  await loadCustomers()
}

async function handleDelete(customer) {
  const confirmed = window.confirm(`確定要刪除客戶「${customer.name || `#${customer.id}`}」嗎？`)
  if (!confirmed) return

  const success = await deleteCustomer(customer.id)
  if (!success) return

  if (customers.value.length === 1 && filters.page > 1) {
    filters.page -= 1
  }

  await loadCustomers()
}

function getStatusLabel(status) {
  return status === 'inactive' ? '停用' : '啟用'
}

function getStatusClass(status) {
  return status === 'inactive'
    ? 'bg-gray-100 text-gray-700'
    : 'bg-emerald-100 text-emerald-700'
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
  await loadCustomers()
})
</script>
