<template>
  <div class="order-create max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">建立訂單</h1>
        <p class="text-sm text-gray-500 mt-1">填寫主檔與品項資料，建立新訂單</p>
      </div>
      <button
        @click="goList"
        class="px-4 py-2 border rounded-lg hover:bg-gray-50"
      >
        返回列表
      </button>
    </div>

    <form class="app-card p-6 space-y-6" @submit.prevent="submitOrder">
      <section class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-700">主檔資訊</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">customer_id *</label>
            <input
              v-model.number="form.customer_id"
              type="number"
              min="1"
              list="customer-options"
              required
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="請輸入 customer_id"
            />
            <datalist id="customer-options">
              <option
                v-for="customer in customerOptions"
                :key="customer.id"
                :value="customer.id"
              >
                {{ customer.name }}
              </option>
            </datalist>
            <p class="text-xs text-gray-500 mt-1" v-if="customerLoading">
              載入客戶清單中...
            </p>
            <p class="text-xs text-gray-500 mt-1" v-else>
              可直接輸入 ID；若清單存在，可從 datalist 選取。
            </p>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">專案名稱</label>
            <input
              v-model.trim="form.project_name"
              type="text"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="例如：2026 Q1 導入專案"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">訂單日期 *</label>
            <input
              v-model="form.order_date"
              type="date"
              required
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">到期日期</label>
            <input
              v-model="form.due_date"
              type="date"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">訂單狀態 *</label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option
                v-for="status in ORDER_STATUS_OPTIONS"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">付款狀態 *</label>
            <select
              v-model="form.payment_status"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option
                v-for="status in PAYMENT_STATUS_OPTIONS"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">稅率 (%)</label>
            <input
              v-model.number="form.tax_rate_percent"
              type="number"
              min="0"
              max="100"
              step="0.01"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">折扣金額</label>
            <input
              v-model.number="form.discount_amount"
              type="number"
              min="0"
              step="0.01"
              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">備註</label>
          <textarea
            v-model.trim="form.notes"
            rows="3"
            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="可填寫訂單補充說明"
          ></textarea>
        </div>
      </section>

      <section class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-700">品項明細</h2>
          <button
            type="button"
            @click="addItem"
            class="px-3 py-2 border rounded-lg hover:bg-gray-50"
          >
            新增品項
          </button>
        </div>

        <div class="overflow-x-auto border rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">名稱 *</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">數量 *</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">單位</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">單價 *</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">小計</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">描述</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                <td class="px-3 py-2">
                  <input
                    v-model.trim="item.name"
                    type="text"
                    class="w-48 px-2 py-1 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="品項名稱"
                    required
                  />
                </td>
                <td class="px-3 py-2">
                  <input
                    v-model.number="item.quantity"
                    type="number"
                    min="1"
                    class="w-24 px-2 py-1 border rounded text-right focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </td>
                <td class="px-3 py-2">
                  <input
                    v-model.trim="item.unit"
                    type="text"
                    class="w-24 px-2 py-1 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="式"
                  />
                </td>
                <td class="px-3 py-2">
                  <input
                    v-model.number="item.unit_price"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-32 px-2 py-1 border rounded text-right focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </td>
                <td class="px-3 py-2 text-right text-sm font-semibold text-gray-700">
                  {{ formatCurrency(itemSubtotal(item)) }}
                </td>
                <td class="px-3 py-2">
                  <input
                    v-model.trim="item.description"
                    type="text"
                    class="w-60 px-2 py-1 border rounded focus:ring-2 focus:ring-blue-500"
                    placeholder="描述（選填）"
                  />
                </td>
                <td class="px-3 py-2">
                  <button
                    type="button"
                    class="text-red-600 hover:text-red-800 disabled:opacity-50"
                    @click="removeItem(index)"
                    :disabled="form.items.length <= 1"
                  >
                    刪除
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-blue-50 rounded-lg p-4 text-sm">
          <div class="flex justify-between mb-1">
            <span class="text-gray-600">小計</span>
            <span>{{ formatCurrency(subtotal) }}</span>
          </div>
          <div class="flex justify-between mb-1">
            <span class="text-gray-600">稅額</span>
            <span>{{ formatCurrency(taxAmount) }}</span>
          </div>
          <div class="flex justify-between mb-1">
            <span class="text-gray-600">折扣</span>
            <span>- {{ formatCurrency(normalizedDiscount) }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-800 border-t pt-2">
            <span>總金額</span>
            <span>{{ formatCurrency(totalAmount) }}</span>
          </div>
        </div>
      </section>

      <p v-if="formError" class="text-sm text-red-600">{{ formError }}</p>

      <div class="flex items-center justify-end gap-2">
        <button
          type="button"
          @click="goList"
          class="px-4 py-2 border rounded-lg hover:bg-gray-50"
        >
          取消
        </button>
        <button
          type="submit"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="submitting"
        >
          {{ submitting ? '建立中...' : '建立訂單' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useOrder } from '../composables/useOrder'
import {
  ORDER_STATUS_OPTIONS,
  PAYMENT_STATUS_OPTIONS,
  createDefaultOrderItem,
  toNumber
} from '../constants'

const router = useRouter()
const {
  customerOptions,
  customerLoading,
  submitting,
  createOrder,
  fetchCustomerOptions
} = useOrder()

const formError = ref('')

const form = reactive({
  customer_id: '',
  project_name: '',
  order_date: todayString(),
  due_date: '',
  notes: '',
  tax_rate_percent: 5,
  discount_amount: 0,
  status: 'pending',
  payment_status: 'unpaid',
  items: [createDefaultOrderItem()]
})

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + itemSubtotal(item), 0)
})

const taxAmount = computed(() => {
  const taxRate = toNumber(form.tax_rate_percent, 0) / 100
  return roundAmount(subtotal.value * taxRate)
})

const normalizedDiscount = computed(() => {
  const discount = toNumber(form.discount_amount, 0)
  return discount < 0 ? 0 : roundAmount(discount)
})

const totalAmount = computed(() => {
  const total = subtotal.value + taxAmount.value - normalizedDiscount.value
  return total < 0 ? 0 : roundAmount(total)
})

function addItem() {
  form.items.push(createDefaultOrderItem())
}

function removeItem(index) {
  if (form.items.length <= 1) return
  form.items.splice(index, 1)
}

function itemSubtotal(item) {
  const quantity = Math.max(1, parseInt(item.quantity, 10) || 1)
  const unitPrice = toNumber(item.unit_price, 0)
  return roundAmount(quantity * unitPrice)
}

function buildPayload() {
  const items = form.items
    .map((item, index) => ({
      sort_order: index + 1,
      name: (item.name || '').trim(),
      description: (item.description || '').trim() || null,
      quantity: Math.max(1, parseInt(item.quantity, 10) || 1),
      unit: (item.unit || '').trim() || null,
      unit_price: roundAmount(toNumber(item.unit_price, 0)),
      notes: (item.notes || '').trim() || null
    }))
    .filter((item) => item.name.length > 0)

  return {
    customer_id: Number(form.customer_id),
    project_name: form.project_name || null,
    order_date: form.order_date,
    due_date: form.due_date || null,
    notes: form.notes || null,
    tax_rate: roundTaxRate(toNumber(form.tax_rate_percent, 0) / 100),
    discount_amount: normalizedDiscount.value,
    status: form.status,
    payment_status: form.payment_status,
    items
  }
}

function validatePayload(payload) {
  if (!Number.isInteger(payload.customer_id) || payload.customer_id <= 0) {
    return 'customer_id 必須為正整數'
  }

  if (!payload.order_date) {
    return '訂單日期不可為空'
  }

  if (payload.due_date && payload.due_date < payload.order_date) {
    return '到期日期不可早於訂單日期'
  }

  if (!Array.isArray(payload.items) || payload.items.length === 0) {
    return '至少需要一個有效品項（名稱不可為空）'
  }

  return ''
}

async function submitOrder() {
  formError.value = ''
  const payload = buildPayload()
  const validationMessage = validatePayload(payload)

  if (validationMessage) {
    formError.value = validationMessage
    return
  }

  const createdOrder = await createOrder(payload)
  if (!createdOrder?.id) {
    formError.value = '建立失敗，請檢查欄位後重試'
    return
  }

  router.push(`/order/detail/${createdOrder.id}`)
}

function goList() {
  router.push('/order/list')
}

function formatCurrency(value) {
  return `${Number(value || 0).toLocaleString()} 元`
}

function todayString() {
  return new Date().toISOString().slice(0, 10)
}

function roundAmount(value) {
  return Number(toNumber(value, 0).toFixed(2))
}

function roundTaxRate(value) {
  return Number(toNumber(value, 0).toFixed(4))
}

onMounted(async () => {
  await fetchCustomerOptions()
})
</script>
