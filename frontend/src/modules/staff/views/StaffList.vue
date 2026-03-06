<template>
  <div class="staff-list">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">員工列表</h1>
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        新增員工
      </button>
    </div>

    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="搜尋姓名、信箱..."
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @input="debouncedSearch"
        />
        <select
          v-model="filters.department"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="loadStaffList"
        >
          <option value="">全部部門</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>
        <button
          @click="resetFilters"
          class="px-4 py-2 border rounded-lg hover:bg-gray-50"
        >
          重置篩選
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">本頁總員工數</div>
        <div class="text-2xl font-bold text-gray-800">{{ stats.total }}</div>
      </div>
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">部門數</div>
        <div class="text-2xl font-bold text-blue-600">{{ departments.length }}</div>
      </div>
    </div>

    <div class="app-card overflow-hidden">
      <LoadingPanel v-if="loading" variant="table" />
      <div v-else-if="staffList.length === 0" class="empty-state">
        尚無員工資料
      </div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">員工編號</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">姓名</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">部門</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">職稱</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">信箱</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="staff in staffList" :key="staff.id" class="hover:bg-gray-50">
            <td class="px-4 py-4 text-sm text-gray-900">{{ staff.employeeId }}</td>
            <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ staff.name }}</td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ getDepartmentName(staff.department) }}</td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ staff.position || '-' }}</td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ staff.email }}</td>
            <td class="px-4 py-4 text-sm">
              <div class="flex gap-3 items-center">
                <button
                  @click="openEditModal(staff)"
                  class="text-blue-600 hover:text-blue-800"
                >
                  編輯
                </button>
                <button
                  @click="handleDelete(staff.id)"
                  class="text-red-600 hover:text-red-800"
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
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">
              {{ isEditMode ? '編輯員工' : '新增員工' }}
            </h2>
            <button
              class="text-gray-400 hover:text-gray-600"
              @click="closeModal"
            >
              取消
            </button>
          </div>

          <form class="space-y-4" @submit.prevent="submitStaff">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1">姓名 *</label>
                <input
                  v-model.trim="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">Email *</label>
                <input
                  v-model.trim="form.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">
                  密碼 {{ isEditMode ? '(留空代表不更新)' : '*' }}
                </label>
                <input
                  v-model="form.password"
                  :required="!isEditMode"
                  type="password"
                  minlength="8"
                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">電話</label>
                <input
                  v-model.trim="form.phone"
                  type="text"
                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">部門</label>
                <input
                  v-model.trim="form.department"
                  type="text"
                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">職稱</label>
                <input
                  v-model.trim="form.position"
                  type="text"
                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border rounded-lg hover:bg-gray-50"
              >
                取消
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {{ isEditMode ? '儲存變更' : '建立員工' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useStaff } from '../composables/useStaff'

const appStore = useAppStore()
const {
  staffList,
  departments,
  loading,
  stats,
  fetchStaffList,
  createStaff,
  updateStaff,
  deleteStaff
} = useStaff()

const filters = ref({
  search: '',
  department: ''
})

const showModal = ref(false)
const editingId = ref(null)
const form = ref({
  name: '',
  email: '',
  password: '',
  phone: '',
  department: '',
  position: ''
})

const isEditMode = computed(() => editingId.value !== null)

const loadStaffList = async () => {
  await fetchStaffList(filters.value)
}

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadStaffList()
  }, 300)
}

const resetFilters = () => {
  filters.value = { search: '', department: '' }
  loadStaffList()
}

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    password: '',
    phone: '',
    department: '',
    position: ''
  }
}

const openCreateModal = () => {
  editingId.value = null
  resetForm()
  showModal.value = true
}

const openEditModal = (staff) => {
  editingId.value = staff.id
  form.value = {
    name: staff.name || '',
    email: staff.email || '',
    password: '',
    phone: staff.phone || '',
    department: staff.department || '',
    position: staff.position || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingId.value = null
  resetForm()
}

const buildPayload = () => {
  const payload = {
    name: form.value.name,
    email: form.value.email,
    phone: form.value.phone || null,
    department: form.value.department || null,
    position: form.value.position || null
  }

  if (form.value.password) {
    payload.password = form.value.password
  }

  return payload
}

const submitStaff = async () => {
  const payload = buildPayload()

  if (!isEditMode.value && !payload.password) {
    appStore.showError('新增員工必須填寫密碼')
    return
  }

  const result = isEditMode.value
    ? await updateStaff(editingId.value, payload)
    : await createStaff(payload)

  if (!result) return

  closeModal()
  await loadStaffList()
}

const handleDelete = async (id) => {
  if (!confirm('確定要刪除此員工？')) return

  const success = await deleteStaff(id)
  if (!success) return
  await loadStaffList()
}

const getDepartmentName = (deptId) => {
  if (!deptId) return '-'
  const department = departments.value.find((item) => item.id === deptId)
  return department?.name || deptId
}

onMounted(async () => {
  await loadStaffList()
})
</script>
