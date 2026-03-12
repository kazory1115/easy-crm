<template>
  <div class="mx-auto mt-10 max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">報價單明細</h1>
        <p class="mt-2 text-sm text-gray-500">正式輸出與寄信都集中在這一頁，不再把 print / Word 當主要流程。</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <button
          v-if="currentQuote && canConvertToOrder"
          :disabled="isConverting"
          class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
          @click="handleConvertToOrder"
        >
          {{ isConverting ? '轉單中...' : '轉為訂單' }}
        </button>
        <button
          v-if="currentQuote"
          class="rounded-lg bg-slate-700 px-4 py-2 text-white hover:bg-slate-800"
          @click="handleDownloadPdf"
        >
          下載 PDF
        </button>
        <button
          v-if="currentQuote"
          class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
          @click="handleDownloadExcel"
        >
          下載 Excel
        </button>
        <button
          v-if="currentQuote"
          class="rounded-lg bg-amber-500 px-4 py-2 text-white hover:bg-amber-600"
          @click="openSendDialog"
        >
          寄送 Email
        </button>
        <button
          v-if="currentQuote"
          class="rounded-lg bg-white px-4 py-2 text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50"
          @click="editQuote"
        >
          編輯
        </button>
        <button
          class="rounded-lg bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300"
          @click="goBack"
        >
          返回
        </button>
      </div>
    </div>

    <LoadingPanel v-if="loading" variant="skeleton" />

    <div v-else-if="currentQuote" class="app-card p-8">
      <div class="mb-8 grid gap-6 md:grid-cols-2">
        <section class="rounded-xl bg-gray-50 p-6">
          <h2 class="mb-4 text-lg font-semibold text-gray-800">客戶資訊</h2>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-4">
              <dt class="text-gray-500">客戶名稱</dt>
              <dd class="font-medium text-gray-800">{{ currentQuote.customer_name }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="text-gray-500">聯絡電話</dt>
              <dd class="text-gray-800">{{ currentQuote.contact_phone || '-' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="text-gray-500">聯絡信箱</dt>
              <dd class="text-gray-800">{{ currentQuote.contact_email || '-' }}</dd>
            </div>
          </dl>
        </section>

        <section class="rounded-xl bg-gray-50 p-6">
          <h2 class="mb-4 text-lg font-semibold text-gray-800">報價資訊</h2>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-4">
              <dt class="text-gray-500">報價單號</dt>
              <dd class="font-medium text-gray-800">{{ currentQuote.quote_number }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="text-gray-500">報價日期</dt>
              <dd class="text-gray-800">{{ formatDate(currentQuote.quote_date) }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="text-gray-500">有效期限</dt>
              <dd class="text-gray-800">{{ formatDate(currentQuote.valid_until) }}</dd>
            </div>
            <div class="flex justify-between gap-4">
              <dt class="text-gray-500">狀態</dt>
              <dd class="font-medium" :class="getStatusClass(currentQuote.status)">{{ getStatusLabel(currentQuote.status) }}</dd>
            </div>
          </dl>
        </section>
      </div>

      <section>
        <h2 class="mb-4 text-lg font-semibold text-gray-800">品項明細</h2>
        <div class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500">#</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">品項</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">描述</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">數量</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">單位</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">單價</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500">小計</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-for="(item, index) in quoteItems" :key="item.id || index">
                <td class="px-4 py-3 text-gray-700">{{ index + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ stripHtml(item.name) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ item.description || '-' }}</td>
                <td class="px-4 py-3 text-right text-gray-700">{{ item.quantity }}</td>
                <td class="px-4 py-3 text-gray-700">{{ item.unit }}</td>
                <td class="px-4 py-3 text-right text-gray-700">{{ formatCurrency(item.price) }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-800">{{ formatCurrency(item.amount || (item.quantity * item.price)) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="mt-6 grid gap-6 lg:grid-cols-[1fr,320px]">
        <div class="rounded-xl bg-gray-50 p-6">
          <h2 class="mb-3 text-lg font-semibold text-gray-800">備註</h2>
          <p class="whitespace-pre-wrap text-sm text-gray-600">{{ currentQuote.notes || '-' }}</p>
        </div>

        <div class="rounded-xl bg-gradient-to-br from-blue-50 to-slate-100 p-6">
          <h2 class="mb-4 text-lg font-semibold text-gray-800">金額摘要</h2>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
              <dt class="text-gray-500">小計</dt>
              <dd class="text-gray-800">{{ formatCurrency(currentQuote.subtotal) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">稅額</dt>
              <dd class="text-gray-800">{{ formatCurrency(currentQuote.tax) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">折扣</dt>
              <dd class="text-gray-800">{{ formatCurrency(currentQuote.discount) }}</dd>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-3 text-base font-semibold">
              <dt class="text-gray-800">總計</dt>
              <dd class="text-blue-700">{{ formatCurrency(currentQuote.total) }}</dd>
            </div>
          </dl>
        </div>
      </section>
    </div>

    <div v-else class="app-card empty-state">
      <i class="fa-solid fa-circle-exclamation mb-4 text-6xl text-red-400"></i>
      <h3 class="mb-2 text-xl font-semibold text-gray-700">找不到報價單</h3>
      <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700" @click="goToList">
        返回列表
      </button>
    </div>

    <div
      v-if="showSendDialog"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @click.self="closeSendDialog"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-start justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">寄送報價單</h2>
            <p class="mt-1 text-sm text-gray-500">會附上 PDF 報價單。</p>
          </div>
          <button class="text-gray-400 hover:text-gray-600" @click="closeSendDialog">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">收件人 Email</label>
            <input v-model.trim="sendForm.email" type="email" class="w-full rounded-lg border px-4 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">主旨</label>
            <input v-model.trim="sendForm.subject" type="text" class="w-full rounded-lg border px-4 py-2" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">訊息</label>
            <textarea v-model="sendForm.message" rows="5" class="w-full rounded-lg border px-4 py-2"></textarea>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" @click="closeSendDialog">取消</button>
          <button
            class="rounded-lg bg-amber-500 px-4 py-2 text-white hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="submitting"
            @click="handleSendQuote"
          >
            {{ submitting ? '寄送中...' : '送出' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useQuote } from '../composables/useQuote'
import { useOrder } from '@/modules/order/composables/useOrder'

const route = useRoute()
const router = useRouter()

const { currentQuote, loading, submitting, fetchQuote, sendQuote, downloadQuotePdf, downloadQuoteExcel } = useQuote()
const { convertQuoteToOrder, submitting: orderSubmitting } = useOrder()

const quoteId = computed(() => Number(route.params.id))
const quoteItems = computed(() => currentQuote.value?.items || [])
const canConvertToOrder = computed(() => currentQuote.value?.status === 'approved')
const isConverting = computed(() => orderSubmitting.value)
const showSendDialog = ref(false)
const sendForm = reactive({
  email: '',
  subject: '',
  message: ''
})

async function loadQuote() {
  await fetchQuote(quoteId.value)
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

function stripHtml(value) {
  const div = document.createElement('div')
  div.innerHTML = value || ''
  return div.textContent || div.innerText || ''
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

async function handleDownloadPdf() {
  if (!currentQuote.value) return
  await downloadQuotePdf(currentQuote.value.id, `${currentQuote.value.quote_number || `quote_${currentQuote.value.id}`}.pdf`)
}

async function handleDownloadExcel() {
  if (!currentQuote.value) return
  await downloadQuoteExcel(currentQuote.value.id, `${currentQuote.value.quote_number || `quote_${currentQuote.value.id}`}.xlsx`)
}

function openSendDialog() {
  if (!currentQuote.value) return
  sendForm.email = currentQuote.value.contact_email || ''
  sendForm.subject = `報價單 ${currentQuote.value.quote_number || ''}`.trim()
  sendForm.message = ''
  showSendDialog.value = true
}

function closeSendDialog() {
  showSendDialog.value = false
}

async function handleSendQuote() {
  if (!currentQuote.value) return

  const updatedQuote = await sendQuote(currentQuote.value.id, {
    email: sendForm.email,
    subject: sendForm.subject,
    message: sendForm.message
  })

  if (updatedQuote) {
    currentQuote.value = updatedQuote
    closeSendDialog()
  }
}

async function handleConvertToOrder() {
  if (!currentQuote.value?.id) return

  const confirmed = window.confirm('確定要把這張報價單轉成訂單？')
  if (!confirmed) return

  const order = await convertQuoteToOrder(currentQuote.value.id)
  if (order?.id) {
    router.push(`/order/detail/${order.id}`)
  }
}

function editQuote() {
  router.push(`/quote/edit/${quoteId.value}`)
}

function goBack() {
  router.back()
}

function goToList() {
  router.push('/quote/list')
}

onMounted(loadQuote)
</script>
