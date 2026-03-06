<template>
  <div class="order-detail max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">訂單詳情</h1>
        <p class="text-sm text-gray-500 mt-1">檢視訂單主檔、品項與金額摘要</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="goEdit"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          :disabled="!currentOrder"
        >
          編輯
        </button>
        <button
          @click="goList"
          class="px-4 py-2 border rounded-lg hover:bg-gray-50"
        >
          返回列表
        </button>
      </div>
    </div>

    <div class="app-card p-6">
      <LoadingPanel v-if="loading" variant="skeleton" />

      <div v-else-if="!currentOrder" class="empty-state">
        <i class="fa-solid fa-circle-exclamation text-6xl text-red-500 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">找不到訂單資料</h3>
        <button
          @click="goList"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          回到列表
        </button>
      </div>

      <div v-else class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <h3 class="text-base font-semibold text-gray-700">主檔資訊</h3>
            <p><span class="text-gray-500">訂單編號：</span>{{ currentOrder.order_number || '-' }}</p>
            <p><span class="text-gray-500">客戶名稱：</span>{{ currentOrder.customer_name || '-' }}</p>
            <p><span class="text-gray-500">專案名稱：</span>{{ currentOrder.project_name || '-' }}</p>
            <p><span class="text-gray-500">訂單日期：</span>{{ formatDate(currentOrder.order_date) }}</p>
            <p><span class="text-gray-500">到期日期：</span>{{ formatDate(currentOrder.due_date) }}</p>
          </div>

          <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <h3 class="text-base font-semibold text-gray-700">狀態資訊</h3>
            <p>
              <span class="text-gray-500 mr-2">訂單狀態：</span>
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="getOrderStatusClass(currentOrder.status)"
              >
                {{ getOrderStatusLabel(currentOrder.status) }}
              </span>
            </p>
            <p>
              <span class="text-gray-500 mr-2">付款狀態：</span>
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="getPaymentStatusClass(currentOrder.payment_status)"
              >
                {{ getPaymentStatusLabel(currentOrder.payment_status) }}
              </span>
            </p>
            <p>
              <span class="text-gray-500">來源報價：</span>
              <template v-if="currentOrder.quote_id">
                <router-link
                  :to="`/quote/detail/${currentOrder.quote_id}`"
                  class="text-blue-600 hover:text-blue-800"
                >
                  #{{ currentOrder.quote_id }}
                </router-link>
              </template>
              <template v-else>-</template>
            </p>
            <p><span class="text-gray-500">建立時間：</span>{{ formatDateTime(currentOrder.created_at) }}</p>
            <p><span class="text-gray-500">更新時間：</span>{{ formatDateTime(currentOrder.updated_at) }}</p>
          </div>
        </div>

        <div>
          <h3 class="text-base font-semibold text-gray-700 mb-3">訂單品項</h3>
          <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">名稱</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">數量</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">單位</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">單價</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">小計</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in currentOrder.items" :key="item.id || index" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm text-gray-700">{{ index + 1 }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900">
                    <div>{{ item.name || '-' }}</div>
                    <div v-if="item.description" class="text-xs text-gray-500 mt-1">
                      {{ item.description }}
                    </div>
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-gray-700">{{ item.quantity }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ item.unit || '-' }}</td>
                  <td class="px-4 py-3 text-sm text-right text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ formatCurrency(item.subtotal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
          <h3 class="text-base font-semibold text-gray-700 mb-3">金額摘要</h3>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">小計</span>
              <span>{{ formatCurrency(currentOrder.subtotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">稅額 ({{ formatPercent(currentOrder.tax_rate) }})</span>
              <span>{{ formatCurrency(currentOrder.tax_amount) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">折扣</span>
              <span>- {{ formatCurrency(currentOrder.discount_amount) }}</span>
            </div>
            <div class="flex justify-between border-t pt-2 text-base font-bold text-gray-800">
              <span>總金額</span>
              <span>{{ formatCurrency(currentOrder.total_amount) }}</span>
            </div>
          </div>
        </div>

        <div v-if="currentOrder.notes" class="bg-gray-50 rounded-lg p-4">
          <h3 class="text-base font-semibold text-gray-700 mb-2">備註</h3>
          <p class="whitespace-pre-wrap text-sm text-gray-700">{{ currentOrder.notes }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useOrder } from '../composables/useOrder'
import {
  getOrderStatusClass,
  getOrderStatusLabel,
  getPaymentStatusClass,
  getPaymentStatusLabel
} from '../constants'

const route = useRoute()
const router = useRouter()

const { currentOrder, loading, fetchOrder } = useOrder()

const orderId = computed(() => Number(route.params.id))

async function loadOrder() {
  if (!orderId.value) return
  await fetchOrder(orderId.value)
}

function goList() {
  router.push('/order/list')
}

function goEdit() {
  if (!orderId.value) return
  router.push(`/order/edit/${orderId.value}`)
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

function formatCurrency(value) {
  return `${Number(value || 0).toLocaleString()} 元`
}

function formatPercent(decimalValue) {
  const numeric = Number(decimalValue || 0)
  return `${(numeric * 100).toFixed(2)}%`
}

onMounted(async () => {
  await loadOrder()
})
</script>
