<template>
  <div class="report-dashboard space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">報表中心</h1>
        <p class="mt-1 text-sm text-gray-500">
          第一版 dashboard，聚合報價、訂單、庫存與匯出任務摘要。
        </p>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <select
          v-model.number="rangeDays"
          class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-teal-500"
          @change="loadDashboard"
        >
          <option :value="30">近 30 天</option>
          <option :value="90">近 90 天</option>
          <option :value="180">近 180 天</option>
        </select>

        <button
          class="rounded-lg border px-4 py-2 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="dashboardLoading"
          @click="loadDashboard"
        >
          重新整理
        </button>

        <button
          class="rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700"
          @click="goExports"
        >
          查看匯出紀錄
        </button>
      </div>
    </div>

    <LoadingPanel v-if="dashboardLoading" variant="skeleton" />

    <template v-else>
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
          <div class="text-sm text-slate-500">報價單總數</div>
          <div class="mt-2 text-3xl font-bold text-slate-900">{{ dashboard.quote.summary.total }}</div>
          <div class="mt-3 text-sm text-slate-600">
            核准 {{ dashboard.quote.summary.approved }} 張，核准率 {{ formatPercent(dashboard.quote.summary.approval_rate) }}
          </div>
        </article>

        <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
          <div class="text-sm text-emerald-700">訂單總金額</div>
          <div class="mt-2 text-3xl font-bold text-emerald-900">{{ formatCurrency(dashboard.order.summary.total_amount) }}</div>
          <div class="mt-3 text-sm text-emerald-700">
            已收款 {{ formatCurrency(dashboard.order.summary.paid_amount) }}，完成率 {{ formatPercent(dashboard.order.summary.completion_rate) }}
          </div>
        </article>

        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
          <div class="text-sm text-amber-700">低庫存 / 缺貨</div>
          <div class="mt-2 text-3xl font-bold text-amber-900">
            {{ dashboard.inventory.summary.low_stock_count }} / {{ dashboard.inventory.summary.out_of_stock_count }}
          </div>
          <div class="mt-3 text-sm text-amber-700">
            追蹤品項 {{ dashboard.inventory.summary.tracked_items }}，總庫存 {{ dashboard.inventory.summary.total_units }}
          </div>
        </article>

        <article class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
          <div class="text-sm text-rose-700">匯出任務</div>
          <div class="mt-2 text-3xl font-bold text-rose-900">{{ dashboard.exports.summary.total }}</div>
          <div class="mt-3 text-sm text-rose-700">
            等待中 {{ dashboard.exports.summary.queued }}，失敗 {{ dashboard.exports.summary.failed }}
          </div>
        </article>
      </div>

      <div class="grid gap-6 xl:grid-cols-2">
        <section class="app-card p-5">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">報價摘要</h2>
              <p class="text-sm text-gray-500">區間 {{ dashboard.filters.start_date }} 至 {{ dashboard.filters.end_date }}</p>
            </div>
            <div class="text-sm text-gray-500">核准金額 {{ formatCurrency(dashboard.quote.summary.approved_amount) }}</div>
          </div>

          <div class="grid gap-3 sm:grid-cols-3">
            <div
              v-for="item in quoteStatusItems"
              :key="item.key"
              class="rounded-xl border border-slate-200 bg-white px-4 py-3"
            >
              <div class="text-xs uppercase tracking-wide text-slate-400">{{ item.label }}</div>
              <div class="mt-2 text-2xl font-semibold text-slate-800">{{ item.value }}</div>
            </div>
          </div>

          <div class="mt-5">
            <div class="mb-2 text-sm font-medium text-gray-700">最近趨勢</div>
            <div v-if="dashboard.quote.trend.length === 0" class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-sm text-gray-500">
              目前區間內沒有報價資料。
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">日期</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">張數</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">金額</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr v-for="item in dashboard.quote.trend" :key="item.period">
                    <td class="px-4 py-3 text-gray-700">{{ formatDate(item.period) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ item.count }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-800">{{ formatCurrency(item.amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section class="app-card p-5">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">訂單摘要</h2>
              <p class="text-sm text-gray-500">已完成 {{ dashboard.order.summary.completed }} 張，未付款 {{ dashboard.order.summary.unpaid }} 張</p>
            </div>
            <div class="text-sm text-gray-500">總訂單 {{ dashboard.order.summary.total }} 張</div>
          </div>

          <div class="grid gap-3 sm:grid-cols-3">
            <div
              v-for="item in orderStatusItems"
              :key="item.key"
              class="rounded-xl border border-slate-200 bg-white px-4 py-3"
            >
              <div class="text-xs uppercase tracking-wide text-slate-400">{{ item.label }}</div>
              <div class="mt-2 text-2xl font-semibold text-slate-800">{{ item.value }}</div>
            </div>
          </div>

          <div class="mt-5">
            <div class="mb-2 text-sm font-medium text-gray-700">最近趨勢</div>
            <div v-if="dashboard.order.trend.length === 0" class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-sm text-gray-500">
              目前區間內沒有訂單資料。
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">日期</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">張數</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">金額</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr v-for="item in dashboard.order.trend" :key="item.period">
                    <td class="px-4 py-3 text-gray-700">{{ formatDate(item.period) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ item.count }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-800">{{ formatCurrency(item.amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>

      <div class="grid gap-6 xl:grid-cols-[1.35fr,1fr]">
        <section class="app-card p-5">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">庫存風險</h2>
              <p class="text-sm text-gray-500">
                倉庫 {{ dashboard.inventory.summary.warehouses }} 個，異動 {{ dashboard.inventory.summary.movement_count }} 筆
              </p>
            </div>
            <div class="text-sm text-gray-500">調整 {{ dashboard.inventory.summary.adjustment_count }} 筆</div>
          </div>

          <div class="grid gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
              <div class="text-xs uppercase tracking-wide text-slate-400">總庫存</div>
              <div class="mt-2 text-2xl font-semibold text-slate-800">{{ dashboard.inventory.summary.total_units }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
              <div class="text-xs uppercase tracking-wide text-slate-400">保留庫存</div>
              <div class="mt-2 text-2xl font-semibold text-slate-800">{{ dashboard.inventory.summary.total_reserved }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
              <div class="text-xs uppercase tracking-wide text-slate-400">庫存筆數</div>
              <div class="mt-2 text-2xl font-semibold text-slate-800">{{ dashboard.inventory.summary.stock_levels }}</div>
            </div>
          </div>

          <div class="mt-5">
            <div class="mb-2 text-sm font-medium text-gray-700">低庫存清單</div>
            <div v-if="dashboard.inventory.low_stock_items.length === 0" class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-sm text-gray-500">
              目前沒有低庫存或缺貨項目。
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">品項</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">倉庫</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">可用</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">安全庫存</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <tr v-for="item in dashboard.inventory.low_stock_items" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="font-medium text-gray-800">{{ item.item_name || '-' }}</div>
                      <div class="text-xs text-gray-500">{{ item.item_category || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                      {{ item.warehouse_name || '-' }}
                    </td>
                    <td class="px-4 py-3 text-right font-medium" :class="item.available_quantity <= 0 ? 'text-rose-600' : 'text-amber-700'">
                      {{ item.available_quantity }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ item.min_level }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section class="app-card p-5">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">匯出任務</h2>
              <p class="text-sm text-gray-500">MVP 先追蹤匯出請求，不直接產出檔案。</p>
            </div>
            <button class="text-sm font-medium text-teal-600 hover:text-teal-700" @click="goExports">
              進入清單
            </button>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
              <div class="text-xs uppercase tracking-wide text-slate-400">等待中</div>
              <div class="mt-2 text-2xl font-semibold text-slate-800">{{ dashboard.exports.summary.queued }}</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
              <div class="text-xs uppercase tracking-wide text-slate-400">已完成</div>
              <div class="mt-2 text-2xl font-semibold text-slate-800">{{ dashboard.exports.summary.done }}</div>
            </div>
          </div>

          <div class="mt-5">
            <div class="mb-2 text-sm font-medium text-gray-700">最近紀錄</div>
            <div v-if="dashboard.exports.recent_records.length === 0" class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-sm text-gray-500">
              目前沒有匯出紀錄。
            </div>
            <div v-else class="space-y-3">
              <article
                v-for="record in dashboard.exports.recent_records"
                :key="record.id"
                class="rounded-xl border border-slate-200 bg-white px-4 py-3"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="font-medium text-gray-800">{{ getReportKeyLabel(record.report_key) }}</div>
                    <div class="mt-1 text-sm text-gray-500">
                      {{ record.user_name }} ・ {{ formatDateTime(record.created_at) }}
                    </div>
                  </div>
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="getExportStatusClass(record.status)"
                  >
                    {{ getExportStatusLabel(record.status) }}
                  </span>
                </div>
              </article>
            </div>
          </div>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useReport } from '../composables/useReport'
import {
  getExportStatusClass,
  getExportStatusLabel,
  getReportKeyLabel
} from '../constants'

const router = useRouter()
const {
  dashboard,
  dashboardLoading,
  fetchDashboard
} = useReport()

const rangeDays = ref(30)

const quoteStatusItems = computed(() => [
  { key: 'draft', label: '草稿', value: dashboard.value.quote.summary.draft },
  { key: 'sent', label: '已送出', value: dashboard.value.quote.summary.sent },
  { key: 'approved', label: '已核准', value: dashboard.value.quote.summary.approved },
  { key: 'rejected', label: '已拒絕', value: dashboard.value.quote.summary.rejected },
  { key: 'expired', label: '已過期', value: dashboard.value.quote.summary.expired },
  { key: 'total_amount', label: '總金額', value: formatCurrency(dashboard.value.quote.summary.total_amount) }
])

const orderStatusItems = computed(() => [
  { key: 'pending', label: '待處理', value: dashboard.value.order.summary.pending },
  { key: 'processing', label: '處理中', value: dashboard.value.order.summary.processing },
  { key: 'completed', label: '已完成', value: dashboard.value.order.summary.completed },
  { key: 'paid', label: '已付款', value: dashboard.value.order.summary.paid },
  { key: 'unpaid', label: '未付款', value: dashboard.value.order.summary.unpaid },
  { key: 'paid_amount', label: '已收款', value: formatCurrency(dashboard.value.order.summary.paid_amount) }
])

async function loadDashboard() {
  await fetchDashboard({
    range_days: rangeDays.value
  })
}

function goExports() {
  router.push('/report/exports')
}

function formatCurrency(value) {
  return `${Number(value || 0).toLocaleString()} 元`
}

function formatPercent(value) {
  return `${Number(value || 0).toFixed(2)}%`
}

function formatDate(value) {
  if (!value) return '-'

  return new Date(value).toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
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
  await loadDashboard()
})
</script>
