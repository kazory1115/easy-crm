<template>
  <div class="customer-form max-w-5xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ isEditMode ? '編輯客戶' : '新增客戶' }}</h1>
        <p class="text-sm text-gray-500 mt-1">
          {{ isEditMode ? '更新 CRM 客戶主檔與聯絡資訊' : '建立新的 CRM 客戶主檔' }}
        </p>
      </div>

      <button
        @click="goBack"
        class="px-4 py-2 border rounded-lg hover:bg-gray-50"
      >
        {{ isEditMode ? '返回詳情' : '返回列表' }}
      </button>
    </div>

    <div class="app-card p-6">
      <LoadingPanel v-if="isEditMode && detailLoading" variant="skeleton" />

      <form v-else class="space-y-6" @submit.prevent="submitForm">
        <section class="space-y-4">
          <h2 class="text-lg font-semibold text-gray-700">基本資料</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-gray-600 mb-1">客戶名稱 *</label>
              <input
                v-model.trim="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
                placeholder="例如：測試客戶股份有限公司"
              />
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">狀態 *</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              >
                <option value="active">啟用</option>
                <option value="inactive">停用</option>
              </select>
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">主要聯絡人</label>
              <input
                v-model.trim="form.contact_person"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">產業</label>
              <input
                v-model.trim="form.industry"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">市話</label>
              <input
                v-model.trim="form.phone"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">手機</label>
              <input
                v-model.trim="form.mobile"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">Email</label>
              <input
                v-model.trim="form.email"
                type="email"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-sm text-gray-600 mb-1">統一編號</label>
              <input
                v-model.trim="form.tax_id"
                type="text"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm text-gray-600 mb-1">網站</label>
              <input
                v-model.trim="form.website"
                type="url"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
                placeholder="https://example.com"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm text-gray-600 mb-1">地址</label>
              <textarea
                v-model.trim="form.address"
                rows="2"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              ></textarea>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm text-gray-600 mb-1">備註</label>
              <textarea
                v-model.trim="form.notes"
                rows="4"
                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
              ></textarea>
            </div>
          </div>
        </section>

        <p v-if="formError" class="text-sm text-red-600">{{ formError }}</p>

        <div class="flex items-center justify-end gap-2">
          <button
            type="button"
            @click="goBack"
            class="px-4 py-2 border rounded-lg hover:bg-gray-50"
          >
            取消
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed"
            :disabled="submitting"
          >
            {{ submitting ? (isEditMode ? '更新中...' : '建立中...') : (isEditMode ? '儲存變更' : '建立客戶') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useCustomer } from '../composables/useCustomer'

const route = useRoute()
const router = useRouter()
const {
  detailLoading,
  submitting,
  fetchCustomer,
  createCustomer,
  updateCustomer
} = useCustomer()

const formError = ref('')

const customerId = computed(() => Number(route.params.id))
const isEditMode = computed(() => route.name === 'CustomerEdit')

const form = reactive({
  name: '',
  contact_person: '',
  phone: '',
  mobile: '',
  email: '',
  tax_id: '',
  website: '',
  industry: '',
  address: '',
  notes: '',
  status: 'active'
})

function hydrateForm(customer) {
  form.name = customer.name || ''
  form.contact_person = customer.contact_person || ''
  form.phone = customer.phone || ''
  form.mobile = customer.mobile || ''
  form.email = customer.email || ''
  form.tax_id = customer.tax_id || ''
  form.website = customer.website || ''
  form.industry = customer.industry || ''
  form.address = customer.address || ''
  form.notes = customer.notes || ''
  form.status = customer.status || 'active'
}

function buildPayload() {
  return {
    name: form.name.trim(),
    contact_person: form.contact_person.trim() || null,
    phone: form.phone.trim() || null,
    mobile: form.mobile.trim() || null,
    email: form.email.trim() || null,
    tax_id: form.tax_id.trim() || null,
    website: form.website.trim() || null,
    industry: form.industry.trim() || null,
    address: form.address.trim() || null,
    notes: form.notes.trim() || null,
    status: form.status
  }
}

function validatePayload(payload) {
  if (!payload.name) {
    return '客戶名稱不可為空'
  }

  return ''
}

async function submitForm() {
  formError.value = ''
  const payload = buildPayload()
  const validationMessage = validatePayload(payload)

  if (validationMessage) {
    formError.value = validationMessage
    return
  }

  if (isEditMode.value) {
    const updatedCustomer = await updateCustomer(customerId.value, payload)
    if (!updatedCustomer?.id) {
      formError.value = '更新失敗，請檢查欄位後重試'
      return
    }

    router.push(`/crm/customers/${updatedCustomer.id}`)
    return
  }

  const createdCustomer = await createCustomer(payload)
  if (!createdCustomer?.id) {
    formError.value = '建立失敗，請檢查欄位後重試'
    return
  }

  router.push(`/crm/customers/${createdCustomer.id}`)
}

function goBack() {
  if (isEditMode.value && customerId.value) {
    router.push(`/crm/customers/${customerId.value}`)
    return
  }

  router.push('/crm/customers')
}

onMounted(async () => {
  if (!isEditMode.value || !customerId.value) return

  const customer = await fetchCustomer(customerId.value)
  if (customer) {
    hydrateForm(customer)
  }
})
</script>
