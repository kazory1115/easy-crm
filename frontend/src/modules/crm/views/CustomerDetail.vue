<template>
  <div class="customer-detail max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">客戶詳情</h1>
        <p class="text-sm text-gray-500 mt-1">查看客戶主檔，以及聯絡人、活動與商機基本資訊</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="goEdit"
          class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
          :disabled="!currentCustomer"
        >
          編輯客戶
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
      <LoadingPanel v-if="detailLoading" variant="skeleton" />

      <div v-else-if="!currentCustomer" class="empty-state">
        <i class="fa-solid fa-circle-exclamation text-6xl text-red-500 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">找不到客戶資料</h3>
        <button
          @click="goList"
          class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
        >
          回到列表
        </button>
      </div>

      <div v-else class="space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
          <div class="bg-gray-50 rounded-lg p-4 space-y-2 xl:col-span-2">
            <div class="flex items-center justify-between gap-4">
              <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ currentCustomer.name }}</h2>
                <p class="text-sm text-gray-500 mt-1">客戶 ID：{{ currentCustomer.id }}</p>
              </div>
              <span
                class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                :class="getStatusClass(currentCustomer.status)"
              >
                {{ getStatusLabel(currentCustomer.status) }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-700">
              <p><span class="text-gray-500">主要聯絡人：</span>{{ currentCustomer.contact_person || '-' }}</p>
              <p><span class="text-gray-500">產業：</span>{{ currentCustomer.industry || '-' }}</p>
              <p><span class="text-gray-500">市話：</span>{{ currentCustomer.phone || '-' }}</p>
              <p><span class="text-gray-500">手機：</span>{{ currentCustomer.mobile || '-' }}</p>
              <p><span class="text-gray-500">Email：</span>{{ currentCustomer.email || '-' }}</p>
              <p><span class="text-gray-500">統編：</span>{{ currentCustomer.tax_id || '-' }}</p>
              <p class="md:col-span-2">
                <span class="text-gray-500">網站：</span>
                <a
                  v-if="currentCustomer.website"
                  :href="currentCustomer.website"
                  target="_blank"
                  rel="noreferrer"
                  class="text-blue-600 hover:text-blue-800"
                >
                  {{ currentCustomer.website }}
                </a>
                <template v-else>-</template>
              </p>
              <p class="md:col-span-2"><span class="text-gray-500">地址：</span>{{ currentCustomer.address || '-' }}</p>
            </div>
          </div>

          <div class="bg-emerald-50 rounded-lg p-4">
            <h3 class="text-base font-semibold text-gray-700 mb-3">CRM 摘要</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">聯絡人</span>
                <span class="font-semibold text-gray-800">{{ currentCustomer.contacts_count }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">活動紀錄</span>
                <span class="font-semibold text-gray-800">{{ currentCustomer.activities_count }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">商機</span>
                <span class="font-semibold text-gray-800">{{ currentCustomer.opportunities_count }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">建立時間</span>
                <span class="font-semibold text-gray-800">{{ formatDateTime(currentCustomer.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">更新時間</span>
                <span class="font-semibold text-gray-800">{{ formatDateTime(currentCustomer.updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="currentCustomer.notes" class="bg-gray-50 rounded-lg p-4">
          <h3 class="text-base font-semibold text-gray-700 mb-2">備註</h3>
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ currentCustomer.notes }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
          <section class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-gray-700">聯絡人</h3>
              <span class="text-xs text-gray-500">最近 {{ contacts.length }} 筆</span>
            </div>

            <div class="border rounded-lg overflow-hidden">
              <LoadingPanel v-if="relatedLoading && contacts.length === 0" variant="table" />

              <div v-else-if="contacts.length === 0" class="p-6 text-sm text-gray-500">
                目前沒有聯絡人資料
              </div>

              <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">姓名</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">職稱</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">聯絡方式</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="contact in contacts" :key="contact.id">
                    <td class="px-4 py-3 text-sm text-gray-900">
                      <div class="flex items-center gap-2">
                        <span>{{ contact.name || '-' }}</span>
                        <span
                          v-if="contact.is_primary"
                          class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700"
                        >
                          主要
                        </span>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ contact.title || '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      <div>{{ contact.phone || contact.mobile || '-' }}</div>
                      <div class="text-xs text-gray-500 mt-1">{{ contact.email || '-' }}</div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-gray-700">商機</h3>
              <button
                @click="goOpportunities"
                class="text-sm text-blue-600 hover:text-blue-800"
              >
                查看完整列表
              </button>
            </div>

            <div class="border rounded-lg overflow-hidden">
              <LoadingPanel v-if="opportunityLoading && opportunities.length === 0" variant="table" />

              <div v-else-if="opportunities.length === 0" class="p-6 text-sm text-gray-500">
                目前沒有商機資料
              </div>

              <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">名稱</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">階段</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">金額</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="opportunity in opportunities" :key="opportunity.id">
                    <td class="px-4 py-3 text-sm text-gray-900">
                      <div>{{ opportunity.name || '-' }}</div>
                      <div class="text-xs text-gray-500 mt-1">{{ formatDate(opportunity.expected_close_date) }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ getOpportunityStageLabel(opportunity.stage) }}</td>
                    <td class="px-4 py-3 text-sm">
                      <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getOpportunityStatusClass(opportunity.status)"
                      >
                        {{ getOpportunityStatusLabel(opportunity.status) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700">{{ formatCurrency(opportunity.amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>

        <section class="space-y-3">
          <h3 class="text-base font-semibold text-gray-700">活動紀錄</h3>

          <div class="border rounded-lg overflow-hidden">
            <LoadingPanel v-if="relatedLoading && activities.length === 0" variant="table" />

            <div v-else-if="activities.length === 0" class="p-6 text-sm text-gray-500">
              目前沒有活動紀錄
            </div>

            <div v-else class="divide-y divide-gray-200 bg-white">
              <div v-for="activity in activities" :key="activity.id" class="p-4">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                        {{ getActivityTypeLabel(activity.type) }}
                      </span>
                      <span class="text-sm font-medium text-gray-800">{{ activity.subject || '未填主旨' }}</span>
                    </div>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ activity.content || '無內容' }}</p>
                  </div>
                  <div class="text-xs text-gray-500 md:text-right">
                    <div>活動時間：{{ formatDateTime(activity.activity_at) }}</div>
                    <div>下次追蹤：{{ formatDateTime(activity.next_action_at) }}</div>
                    <div>記錄者：{{ activity.user_name || '-' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useCustomer } from '../composables/useCustomer'
import { useOpportunity } from '../composables/useOpportunity'

const route = useRoute()
const router = useRouter()

const {
  currentCustomer,
  contacts,
  activities,
  detailLoading,
  relatedLoading,
  fetchCustomer,
  fetchCustomerContacts,
  fetchCustomerActivities
} = useCustomer()

const {
  opportunities,
  loading: opportunityLoading,
  fetchOpportunities
} = useOpportunity()

const customerId = computed(() => Number(route.params.id))

async function loadDetail() {
  if (!customerId.value) return

  const customer = await fetchCustomer(customerId.value)
  if (!customer) return

  await Promise.all([
    fetchCustomerContacts(customerId.value, {
      per_page: 10,
      sort_by: 'created_at',
      sort_order: 'desc'
    }),
    fetchCustomerActivities(customerId.value, {
      per_page: 10,
      sort_by: 'activity_at',
      sort_order: 'desc'
    }),
    fetchOpportunities({
      customer_id: customerId.value,
      per_page: 10,
      sort_by: 'updated_at',
      sort_order: 'desc'
    })
  ])
}

function goList() {
  router.push('/crm/customers')
}

function goEdit() {
  if (!customerId.value) return
  router.push(`/crm/customers/${customerId.value}/edit`)
}

function goOpportunities() {
  if (!customerId.value) {
    router.push('/crm/opportunities')
    return
  }

  router.push({
    path: '/crm/opportunities',
    query: { customer_id: String(customerId.value) }
  })
}

function getStatusLabel(status) {
  return status === 'inactive' ? '停用' : '啟用'
}

function getStatusClass(status) {
  return status === 'inactive'
    ? 'bg-gray-100 text-gray-700'
    : 'bg-emerald-100 text-emerald-700'
}

function getActivityTypeLabel(type) {
  const map = {
    call: '電話',
    email: 'Email',
    meeting: '會議',
    note: '備註',
    follow_up: '追蹤',
    other: '其他'
  }

  return map[type] || '其他'
}

function getOpportunityStageLabel(stage) {
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

function getOpportunityStatusLabel(status) {
  const map = {
    open: '進行中',
    won: '已成交',
    lost: '已失敗'
  }

  return map[status] || status || '-'
}

function getOpportunityStatusClass(status) {
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

onMounted(async () => {
  await loadDetail()
})
</script>
