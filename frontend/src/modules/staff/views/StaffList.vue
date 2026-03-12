<template>
  <div class="staff-list">
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">員工管理</h1>
        <p class="mt-1 text-sm text-gray-500">管理員工基本資料、角色與模組權限。</p>
      </div>
      <button
        @click="openCreateModal"
        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
      >
        新增員工
      </button>
    </div>

    <div class="app-card mb-6 p-4">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <input
          v-model="filters.search"
          type="text"
          placeholder="搜尋姓名或 Email"
          class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-blue-500"
          @input="debouncedSearch"
        />
        <select
          v-model="filters.department"
          class="rounded-lg border px-4 py-2 focus:ring-2 focus:ring-blue-500"
          @change="loadStaffList"
        >
          <option value="">全部部門</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>
        <button
          @click="resetFilters"
          class="rounded-lg border px-4 py-2 hover:bg-gray-50"
        >
          重設條件
        </button>
      </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">員工總數</div>
        <div class="text-2xl font-bold text-gray-800">{{ stats.total }}</div>
      </div>
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">部門數量</div>
        <div class="text-2xl font-bold text-blue-600">{{ departments.length }}</div>
      </div>
    </div>

    <div class="app-card overflow-hidden">
      <LoadingPanel v-if="loading" variant="table" />
      <div v-else-if="staffList.length === 0" class="empty-state">
        目前沒有員工資料
      </div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">員工編號</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">姓名</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">部門 / 職稱</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">角色</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">直接權限</th>
            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">操作</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="staff in staffList" :key="staff.id" class="hover:bg-gray-50">
            <td class="px-4 py-4 text-sm text-gray-900">{{ staff.employeeId }}</td>
            <td class="px-4 py-4 text-sm">
              <div class="font-medium text-gray-900">{{ staff.name }}</div>
              <div class="text-gray-500">{{ staff.email }}</div>
            </td>
            <td class="px-4 py-4 text-sm text-gray-500">
              <div>{{ staff.department || '-' }}</div>
              <div>{{ staff.position || '-' }}</div>
            </td>
            <td class="px-4 py-4 text-sm text-gray-500">
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="role in staff.roles"
                  :key="role"
                  class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-700"
                >
                  {{ role }}
                </span>
                <span v-if="staff.roles.length === 0">-</span>
              </div>
            </td>
            <td class="px-4 py-4 text-sm text-gray-500">
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="permission in staff.directPermissions.slice(0, 3)"
                  :key="permission"
                  class="rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-800"
                >
                  {{ permission }}
                </span>
                <span v-if="staff.directPermissions.length > 3" class="text-xs text-gray-400">
                  +{{ staff.directPermissions.length - 3 }}
                </span>
                <span v-if="staff.directPermissions.length === 0">-</span>
              </div>
            </td>
            <td class="px-4 py-4 text-sm">
              <div class="flex items-center gap-3">
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
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    >
      <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-lg bg-white shadow-xl">
        <div class="p-6">
          <div class="mb-4 flex items-start justify-between gap-4">
            <div>
              <h2 class="text-xl font-bold text-gray-800">
                {{ isEditMode ? '編輯員工' : '新增員工' }}
              </h2>
              <p class="mt-1 text-sm text-gray-500">
                角色代表預設權限，直接權限用來做單一員工覆蓋。
              </p>
            </div>
            <button class="text-gray-400 hover:text-gray-600" @click="closeModal">關閉</button>
          </div>

          <form class="space-y-6" @submit.prevent="submitStaff">
            <section>
              <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">基本資料</h3>
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm text-gray-600">姓名 *</label>
                  <input
                    v-model.trim="form.name"
                    type="text"
                    required
                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm text-gray-600">Email *</label>
                  <input
                    v-model.trim="form.email"
                    type="email"
                    required
                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm text-gray-600">
                    密碼 {{ isEditMode ? '(留空表示不變更)' : '*' }}
                  </label>
                  <input
                    v-model="form.password"
                    :required="!isEditMode"
                    type="password"
                    minlength="8"
                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm text-gray-600">電話</label>
                  <input
                    v-model.trim="form.phone"
                    type="text"
                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm text-gray-600">部門</label>
                  <input
                    v-model.trim="form.department"
                    type="text"
                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm text-gray-600">職稱</label>
                  <input
                    v-model.trim="form.position"
                    type="text"
                    class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
            </section>

            <section>
              <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">角色</h3>
              <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <label
                  v-for="role in roleOptions"
                  :key="role"
                  class="flex items-center gap-3 rounded-lg border p-3 hover:bg-gray-50"
                >
                  <input
                    :checked="form.roles.includes(role)"
                    type="checkbox"
                    class="h-4 w-4"
                    @change="toggleSelection(form.roles, role)"
                  />
                  <span class="text-sm text-gray-700">{{ role }}</span>
                </label>
              </div>
            </section>

            <section>
              <div class="mb-3 flex items-center justify-between gap-4">
                <div>
                  <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">直接權限</h3>
                  <p class="mt-1 text-xs text-gray-400">只勾需要覆蓋角色預設的例外權限。</p>
                </div>
                <button
                  type="button"
                  class="text-sm text-gray-500 hover:text-gray-700"
                  @click="form.directPermissions = []"
                >
                  清空直接權限
                </button>
              </div>

              <div class="space-y-4">
                <div
                  v-for="module in modulePermissionGroups"
                  :key="module.id"
                  class="rounded-xl border border-gray-200 p-4"
                >
                  <div class="mb-3 flex items-center justify-between gap-4">
                    <div>
                      <div class="font-medium text-gray-900">{{ module.name }}</div>
                      <div class="text-xs text-gray-400">{{ module.id }}</div>
                    </div>
                    <button
                      type="button"
                      class="text-xs text-blue-600 hover:text-blue-800"
                      @click="toggleModulePermissions(module)"
                    >
                      {{ isAllModulePermissionsSelected(module) ? '取消全選' : '全選' }}
                    </button>
                  </div>

                  <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <label
                      v-for="permission in module.permissions"
                      :key="permission.name"
                      class="flex items-start gap-3 rounded-lg border p-3 hover:bg-gray-50"
                    >
                      <input
                        :checked="form.directPermissions.includes(permission.name)"
                        type="checkbox"
                        class="mt-0.5 h-4 w-4"
                        @change="toggleSelection(form.directPermissions, permission.name)"
                      />
                      <div>
                        <div class="text-sm font-medium text-gray-800">{{ permission.label }}</div>
                        <div class="text-xs text-gray-400">{{ permission.name }}</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </section>

            <div class="flex justify-end gap-2 border-t pt-4">
              <button
                type="button"
                @click="closeModal"
                class="rounded-lg border px-4 py-2 hover:bg-gray-50"
              >
                取消
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
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
import { computed, onMounted, ref } from 'vue'
import { useAppStore } from '@/stores/app'
import LoadingPanel from '@/components/LoadingPanel.vue'
import { useStaff } from '../composables/useStaff'

const appStore = useAppStore()
const {
  staffList,
  departments,
  loading,
  stats,
  roleOptions,
  modulePermissionGroups,
  fetchStaffList,
  fetchStaff,
  fetchAccessControlOptions,
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
  position: '',
  roles: [],
  directPermissions: []
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
    position: '',
    roles: [],
    directPermissions: []
  }
}

const openCreateModal = async () => {
  editingId.value = null
  resetForm()
  await fetchAccessControlOptions()
  showModal.value = true
}

const openEditModal = async (staff) => {
  editingId.value = staff.id
  await fetchAccessControlOptions()
  const detail = await fetchStaff(staff.id)

  if (!detail) {
    return
  }

  form.value = {
    name: detail.name || '',
    email: detail.email || '',
    password: '',
    phone: detail.phone || '',
    department: detail.department || '',
    position: detail.position || '',
    roles: [...(detail.roles || [])],
    directPermissions: [...(detail.directPermissions || [])]
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
    position: form.value.position || null,
    roles: [...form.value.roles],
    direct_permissions: [...form.value.directPermissions]
  }

  if (form.value.password) {
    payload.password = form.value.password
  }

  return payload
}

const toggleSelection = (target, value) => {
  const index = target.indexOf(value)
  if (index === -1) {
    target.push(value)
    return
  }
  target.splice(index, 1)
}

const isAllModulePermissionsSelected = (module) => {
  return module.permissions.every((permission) => form.value.directPermissions.includes(permission.name))
}

const toggleModulePermissions = (module) => {
  const isAllSelected = isAllModulePermissionsSelected(module)

  module.permissions.forEach((permission) => {
    const exists = form.value.directPermissions.includes(permission.name)

    if (isAllSelected && exists) {
      form.value.directPermissions = form.value.directPermissions.filter((item) => item !== permission.name)
    }

    if (!isAllSelected && !exists) {
      form.value.directPermissions.push(permission.name)
    }
  })
}

const submitStaff = async () => {
  const payload = buildPayload()

  if (!isEditMode.value && !payload.password) {
    appStore.showError('新增員工時必須設定密碼')
    return
  }

  if (payload.roles.length === 0) {
    appStore.showError('至少要指定一個角色')
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
  if (!confirm('確定要刪除這位員工嗎？')) return

  const success = await deleteStaff(id)
  if (!success) return
  await loadStaffList()
}

onMounted(async () => {
  await fetchAccessControlOptions()
  await loadStaffList()
})
</script>
