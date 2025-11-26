<template>
  <div class="staff-list">
    <!-- 頁面標題與操作 -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">員工列表</h1>
      <div class="flex gap-2">
        <button
          v-if="selectedIds.length > 0"
          @click="handleBatchDelete"
          class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
        >
          刪除選取 ({{ selectedIds.length }})
        </button>
        <button
          @click="handleExport"
          class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
        >
          匯出
        </button>
        <router-link
          to="/staff/create"
          class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
        >
          新增員工
        </router-link>
      </div>
    </div>

    <!-- 搜尋與篩選 -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="搜尋姓名、信箱、員工編號..."
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
        <select
          v-model="filters.status"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="loadStaffList"
        >
          <option value="">全部狀態</option>
          <option value="active">在職</option>
          <option value="inactive">離職</option>
          <option value="suspended">停權</option>
        </select>
        <button
          @click="resetFilters"
          class="px-4 py-2 border rounded-lg hover:bg-gray-50"
        >
          重置篩選
        </button>
      </div>
    </div>

    <!-- 統計卡片 -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">總員工數</div>
        <div class="text-2xl font-bold text-gray-800">{{ stats.total }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">在職</div>
        <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">離職</div>
        <div class="text-2xl font-bold text-gray-400">{{ stats.inactive }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">停權</div>
        <div class="text-2xl font-bold text-red-600">{{ stats.suspended }}</div>
      </div>
    </div>

    <!-- 員工列表 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-500">
        載入中...
      </div>
      <div v-else-if="staffList.length === 0" class="p-8 text-center text-gray-500">
        尚無員工資料
      </div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left">
              <input
                type="checkbox"
                :checked="isAllSelected"
                @change="toggleSelectAll"
                class="rounded"
              />
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">員工編號</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">姓名</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">部門</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">職稱</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">信箱</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">狀態</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="staff in staffList" :key="staff.id" class="hover:bg-gray-50">
            <td class="px-4 py-4">
              <input
                type="checkbox"
                :checked="selectedIds.includes(staff.id)"
                @change="toggleSelect(staff.id)"
                class="rounded"
              />
            </td>
            <td class="px-4 py-4 text-sm text-gray-900">{{ staff.employeeId }}</td>
            <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ staff.name }}</td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ getDepartmentName(staff.department) }}</td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ staff.position }}</td>
            <td class="px-4 py-4 text-sm text-gray-500">{{ staff.email }}</td>
            <td class="px-4 py-4">
              <span :class="getStatusClass(staff.status)">
                {{ getStatusText(staff.status) }}
              </span>
            </td>
            <td class="px-4 py-4 text-sm">
              <div class="flex gap-2">
                <router-link
                  :to="`/staff/detail/${staff.id}`"
                  class="text-blue-600 hover:text-blue-800"
                >
                  檢視
                </router-link>
                <router-link
                  :to="`/staff/edit/${staff.id}`"
                  class="text-green-600 hover:text-green-800"
                >
                  編輯
                </router-link>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStaff } from '../composables/useStaff'

const {
  staffList,
  departments,
  loading,
  stats,
  fetchStaffList,
  fetchDepartments,
  deleteStaff,
  batchDeleteStaff,
  exportStaff
} = useStaff()

// 篩選條件
const filters = ref({
  search: '',
  department: '',
  status: ''
})

// 選取狀態
const selectedIds = ref([])

const isAllSelected = computed(() => {
  return staffList.value.length > 0 && selectedIds.value.length === staffList.value.length
})

// 載入員工列表
const loadStaffList = async () => {
  await fetchStaffList(filters.value)
}

// 防抖搜尋
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadStaffList()
  }, 300)
}

// 重置篩選
const resetFilters = () => {
  filters.value = { search: '', department: '', status: '' }
  loadStaffList()
}

// 選取操作
const toggleSelect = (id) => {
  const index = selectedIds.value.indexOf(id)
  if (index === -1) {
    selectedIds.value.push(id)
  } else {
    selectedIds.value.splice(index, 1)
  }
}

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = staffList.value.map(s => s.id)
  }
}

// 刪除操作
const handleDelete = async (id) => {
  if (confirm('確定要刪除此員工？')) {
    await deleteStaff(id)
    await loadStaffList()
  }
}

const handleBatchDelete = async () => {
  if (confirm(`確定要刪除 ${selectedIds.value.length} 位員工？`)) {
    await batchDeleteStaff(selectedIds.value)
    selectedIds.value = []
    await loadStaffList()
  }
}

// 匯出
const handleExport = async () => {
  await exportStaff('excel', selectedIds.value)
}

// 工具函數
const getDepartmentName = (deptId) => {
  const dept = departments.value.find(d => d.id === deptId)
  return dept?.name || deptId
}

const getStatusText = (status) => {
  const map = { active: '在職', inactive: '離職', suspended: '停權' }
  return map[status] || status
}

const getStatusClass = (status) => {
  const base = 'px-2 py-1 text-xs rounded-full'
  const map = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    suspended: 'bg-red-100 text-red-800'
  }
  return `${base} ${map[status] || ''}`
}

onMounted(async () => {
  await fetchDepartments()
  await loadStaffList()
})
</script>
