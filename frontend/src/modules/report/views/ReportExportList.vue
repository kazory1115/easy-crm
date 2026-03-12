<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">匯出紀錄</h1>
        <p class="mt-1 text-sm text-gray-500">
          先把匯出請求與狀態留痕，後續要接 queue、storage、真正檔案下載時不需要重做 UI。
        </p>
      </div>

      <div class="flex flex-wrap gap-3">
        <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" @click="goDashboard">
          返回 Dashboard
        </button>
        <button
          v-if="canRequestExport"
          class="rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="submitting"
          @click="requestExport('quote_summary')"
        >
          建立報價摘要匯出
        </button>
        <button
          v-if="canRequestExport"
          class="rounded-lg bg-slate-800 px-4 py-2 text-white hover:bg-slate-900 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="submitting"
          @click="requestExport('order_summary')"
        >
          建立訂單摘要匯出
        </button>
      </div>
    </div>

    <div class="rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
      現階段只建立匯出任務紀錄，不真的產出檔案。這是刻意保留的 MVP 範圍，先把流程與資料結構站穩。
    </div>

    <div class="app-card p-4">
      <div class="grid gap-3 md:grid-cols-4 xl:grid-cols-5">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋報表類型、建立者或檔案路徑"
          class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-teal-500"
          @input="handleSearchInput"
        />

        <select
          v-model="filters.report_key"
          class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-teal-500"
          @change="handleFilterChange"
        >
          <option value="">全部報表類型</option>
          <option v-for="option in REPORT_KEY_OPTIONS" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>

        <select
          v-model="filters.status"
          class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-teal-500"
          @change="handleFilterChange"
        >
          <option value="">全部狀態</option>
          <option v-for="option in EXPORT_STATUS_OPTIONS" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>

        <select
          v-model="filters.format"
          class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-teal-500"
          @change="handleFilterChange"
        >
          <option value="">全部格式</option>
          <option v-for="option in EXPORT_FORMAT_OPTIONS" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>

        <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" @click="resetFilters">
          重設篩選
        </button>
      </div>
    </div>

    <div class="app-card overflow-hidden">
      <LoadingPanel v-if="exportLoading" variant="table" />

      <div v-else-if="exportRecords.length === 0" class="empty-state">
        <i class="fa-solid fa-folder-open mb-4 text-6xl text-gray-300"></i>
        <h3 class="mb-2 text-xl font-semibold text-gray-700">目前沒有匯出紀錄</h3>
        <p class="mb-5 text-sm text-gray-500">你可以先建立一筆匯出任務，之後這裡會成為下載與追蹤狀態的入口。</p>
        <button
          v-if="canRequestExport"
          class="rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700"
          @click="requestExport('inventory_snapshot')"
        >
          建立庫存快照匯出
        </button>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">建立時間</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">報表類型</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">格式</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">狀態</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">建立者</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">篩選條件</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">完成時間</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">檔案路徑</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white text-sm">
              <tr v-for="record in exportRecords" :key="record.id" class="hover:bg-gray-50">
                <td class="px-4 py-4 text-gray-700">{{ formatDateTime(record.created_at) }}</td>
                <td class="px-4 py-4">
                  <div class="font-medium text-gray-800">{{ getReportKeyLabel(record.report_key) }}</div>
                  <div class="mt-1 text-xs text-gray-500">#{{ record.id }}</div>
                </td>
                <td class="px-4 py-4 text-gray-700">{{ getFormatLabel(record.format) }}</td>
                <td class="px-4 py-4">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="getExportStatusClass(record.status)"
                  >
                    {{ getExportStatusLabel(record.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-gray-700">{{ record.user_name }}</td>
                <td class="px-4 py-4 text-gray-600">{{ formatFilters(record.filters) }}</td>
                <td class="px-4 py-4 text-gray-700">{{ formatDateTime(record.completed_at) }}</td>
                <td class="px-4 py-4 text-gray-600">{{ record.file_path || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="exportPagination.total > 0"
          class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 md:flex-row md:items-center md:justify-between"
        >
          <div class="text-sm text-gray-600">
            共 {{ exportPagination.total }} 筆，第 {{ exportPagination.currentPage }} / {{ exportPagination.lastPage }} 頁
          </div>
          <div class="flex items-center gap-2">
            <button
              class="rounded border px-3 py-1 hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="exportPagination.currentPage <= 1 || exportLoading"
              @click="goPage(exportPagination.currentPage - 1)"
            >
              上一頁
            </button>
            <button
              class="rounded border px-3 py-1 hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="exportPagination.currentPage >= exportPagination.lastPage || exportLoading"
              @click="goPage(exportPagination.currentPage + 1)"
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
import { computed, onBeforeUnmount, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useAuthStore } from '@/stores/auth'
import { useReport } from '../composables/useReport'
import {
  EXPORT_FORMAT_OPTIONS,
  EXPORT_STATUS_OPTIONS,
  REPORT_KEY_OPTIONS,
  getExportStatusClass,
  getExportStatusLabel,
  getFormatLabel,
  getReportKeyLabel
} from '../constants'

const router = useRouter()
const authStore = useAuthStore()
const {
  exportRecords,
  exportPagination,
  exportLoading,
  submitting,
  fetchExportRecords,
  createExportRecord
} = useReport()

const canRequestExport = computed(() => authStore.hasPermission('report.export'))

const filters = reactive({
  page: 1,
  per_page: 15,
  search: '',
  report_key: '',
  status: '',
  format: ''
})

let searchTimer = null

function buildQuery() {
  return {
    page: filters.page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    report_key: filters.report_key || undefined,
    status: filters.status || undefined,
    format: filters.format || undefined,
    sort_by: 'created_at',
    sort_order: 'desc'
  }
}

async function loadRecords() {
  await fetchExportRecords(buildQuery())
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(async () => {
    filters.page = 1
    await loadRecords()
  }, 300)
}

async function handleFilterChange() {
  filters.page = 1
  await loadRecords()
}

async function resetFilters() {
  filters.page = 1
  filters.search = ''
  filters.report_key = ''
  filters.status = ''
  filters.format = ''
  await loadRecords()
}

async function goPage(page) {
  if (page < 1 || page > exportPagination.value.lastPage || exportLoading.value) return
  filters.page = page
  await loadRecords()
}

function goDashboard() {
  router.push('/report/dashboard')
}

async function requestExport(reportKey) {
  if (!canRequestExport.value) return

  const record = await createExportRecord({
    report_key: reportKey,
    format: 'xlsx',
    filters: {
      source: 'report_export_list',
      requested_at: new Date().toISOString()
    }
  })

  if (!record) return

  filters.page = 1
  await loadRecords()
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

function formatFilters(filtersValue) {
  if (!filtersValue || typeof filtersValue !== 'object') return '-'

  const entries = Object.entries(filtersValue)
  if (entries.length === 0) return '-'

  return entries
    .map(([key, value]) => `${key}: ${Array.isArray(value) ? value.join(', ') : value}`)
    .join(' / ')
}

onMounted(loadRecords)

onBeforeUnmount(() => {
  clearTimeout(searchTimer)
})
</script>
