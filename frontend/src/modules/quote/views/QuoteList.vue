<template>
  <div class="mx-auto mt-10 max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">報價單列表</h1>
        <p class="mt-2 text-sm text-gray-500">主要輸出流程改為正式 PDF / Excel / Email API，不再依賴臨時 Word 匯出。</p>
      </div>

      <button
        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
        @click="router.push('/quote/create')"
      >
        新增報價單
      </button>
    </div>

    <div class="app-card p-4">
      <div class="grid gap-3 md:grid-cols-4">
        <input
          v-model.trim="filters.search"
          type="text"
          placeholder="搜尋客戶、專案或報價單號"
          class="rounded-lg border px-4 py-2"
          @input="loadQuotes"
        />
        <select v-model="filters.status" class="rounded-lg border px-4 py-2" @change="loadQuotes">
          <option value="">全部狀態</option>
          <option value="draft">草稿</option>
          <option value="sent">已送出</option>
          <option value="approved">已核准</option>
          <option value="rejected">已拒絕</option>
          <option value="expired">已過期</option>
        </select>
        <input v-model="filters.start_date" type="date" class="rounded-lg border px-4 py-2" @change="loadQuotes" />
        <input v-model="filters.end_date" type="date" class="rounded-lg border px-4 py-2" @change="loadQuotes" />
      </div>
    </div>

    <LoadingPanel v-if="loading" variant="table" />

    <div v-else-if="quotes.length === 0" class="app-card empty-state">
      <i class="fa-solid fa-inbox mb-4 text-6xl text-gray-300"></i>
      <h3 class="mb-2 text-xl font-semibold text-gray-700">目前沒有報價單</h3>
      <p class="text-sm text-gray-500">你可以先建立一張報價單，之後從明細頁下載 PDF / Excel 或直接寄信。</p>
    </div>

    <div v-else class="space-y-4">
      <article
        v-for="quote in quotes"
        :key="quote.id"
        class="app-card p-6"
      >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="space-y-3">
            <div>
              <h2 class="text-xl font-semibold text-gray-800">{{ quote.customer_name }}</h2>
              <p class="text-sm text-gray-500">{{ quote.project_name || '未填專案名稱' }}</p>
            </div>
            <div class="grid gap-2 text-sm text-gray-600 md:grid-cols-2">
              <div>報價單號：{{ quote.quote_number || '-' }}</div>
              <div>報價日期：{{ formatDate(quote.quote_date) }}</div>
              <div>聯絡電話：{{ quote.contact_phone || '-' }}</div>
              <div>狀態：<span :class="getStatusClass(quote.status)">{{ getStatusLabel(quote.status) }}</span></div>
            </div>
          </div>

          <div class="text-right">
            <div class="text-sm text-gray-500">總計</div>
            <div class="text-2xl font-bold text-blue-700">{{ formatCurrency(quote.total) }}</div>
          </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
          <button class="rounded-lg bg-slate-700 px-4 py-2 text-sm text-white hover:bg-slate-800" @click="handleDownloadPdf(quote)">
            PDF
          </button>
          <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700" @click="handleDownloadExcel(quote)">
            Excel
          </button>
          <button class="rounded-lg bg-white px-4 py-2 text-sm text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50" @click="viewQuote(quote.id)">
            明細
          </button>
          <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700" @click="editQuote(quote.id)">
            編輯
          </button>
          <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm text-white hover:bg-rose-700" @click="deleteQuoteData(quote.id)">
            刪除
          </button>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useQuote } from '../composables/useQuote'

const router = useRouter()
const { quotes, loading, fetchQuotes, deleteQuote, downloadQuotePdf, downloadQuoteExcel } = useQuote()

const filters = reactive({
  search: '',
  status: '',
  start_date: '',
  end_date: ''
})

async function loadQuotes() {
  await fetchQuotes({
    search: filters.search || undefined,
    status: filters.status || undefined,
    start_date: filters.start_date || undefined,
    end_date: filters.end_date || undefined,
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: 100
  })
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
  return `NT$ ${Number(value || 0).toLocaleString()}`
}

function getStatusLabel(status) {
  const map = {
    draft: '草稿',
    sent: '已送出',
    approved: '已核准',
    rejected: '已拒絕',
    expired: '已過期'
  }

  return map[status] || status || '-'
}

function getStatusClass(status) {
  const map = {
    draft: 'text-slate-600',
    sent: 'text-blue-600',
    approved: 'text-emerald-600',
    rejected: 'text-rose-600',
    expired: 'text-amber-600'
  }

  return map[status] || 'text-slate-600'
}

function viewQuote(id) {
  router.push(`/quote/detail/${id}`)
}

function editQuote(id) {
  router.push(`/quote/edit/${id}`)
}

async function handleDownloadPdf(quote) {
  await downloadQuotePdf(quote.id, `${quote.quote_number || `quote_${quote.id}`}.pdf`)
}

async function handleDownloadExcel(quote) {
  await downloadQuoteExcel(quote.id, `${quote.quote_number || `quote_${quote.id}`}.xlsx`)
}

async function deleteQuoteData(id) {
  const confirmed = window.confirm('確定要刪除這張報價單？')
  if (!confirmed) return

  await deleteQuote(id)
  await loadQuotes()
}

onMounted(loadQuotes)
</script>
