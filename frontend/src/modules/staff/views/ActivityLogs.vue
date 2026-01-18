<template>
  <div class="activity-logs">
    <!-- 頁面標題與操作 -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">操作紀錄</h1>
      <div class="flex gap-2">
        <button
          @click="handleRefresh"
          class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
        >
          重新整理
        </button>
        <button
          v-if="viewMode === 'all'"
          @click="switchToMyLogs"
          class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
        >
          只看我的紀錄
        </button>
        <button
          v-else
          @click="switchToAllLogs"
          class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
        >
          查看全部紀錄
        </button>
      </div>
    </div>

    <!-- 搜尋與篩選 -->
    <div class="app-card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="搜尋描述、IP..."
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @input="debouncedSearch"
        />
        <select
          v-model="filters.event"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="loadActivityLogs"
        >
          <option value="">全部事件</option>
          <option value="created">新增</option>
          <option value="updated">更新</option>
          <option value="deleted">刪除</option>
          <option value="viewed">查看</option>
          <option value="exported">匯出</option>
        </select>
        <select
          v-model="filters.module"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="loadActivityLogs"
        >
          <option value="">全部模組</option>
          <option value="quote">報價單</option>
          <option value="template">範本</option>
          <option value="customer">客戶</option>
          <option value="user">員工</option>
          <option value="item">項目</option>
        </select>
        <input
          v-model="filters.start_date"
          type="date"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="loadActivityLogs"
        />
        <input
          v-model="filters.end_date"
          type="date"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          @change="loadActivityLogs"
        />
      </div>
      <div class="mt-4 flex justify-between items-center">
        <button
          @click="resetFilters"
          class="px-4 py-2 border rounded-lg hover:bg-gray-50"
        >
          重置篩選
        </button>
        <div class="text-sm text-gray-500">
          共 {{ pagination.total }} 筆紀錄
        </div>
      </div>
    </div>

    <!-- 統計卡片 -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">總紀錄數</div>
        <div class="text-2xl font-bold text-gray-800">
          {{ stats.total || 0 }}
        </div>
      </div>
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">新增</div>
        <div class="text-2xl font-bold text-green-600">
          {{ stats.byEvent?.created || 0 }}
        </div>
      </div>
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">更新</div>
        <div class="text-2xl font-bold text-blue-600">
          {{ stats.byEvent?.updated || 0 }}
        </div>
      </div>
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">刪除</div>
        <div class="text-2xl font-bold text-red-600">
          {{ stats.byEvent?.deleted || 0 }}
        </div>
      </div>
      <div class="app-card p-4">
        <div class="text-sm text-gray-500">其他</div>
        <div class="text-2xl font-bold text-gray-600">
          {{ otherEventsCount }}
        </div>
      </div>
    </div>

    <!-- 操作紀錄列表 -->
    <div class="app-card overflow-hidden">
      <LoadingPanel v-if="loading" variant="table" />
      <div v-else-if="logs.length === 0" class="empty-state">尚無操作紀錄</div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              時間
            </th>
            <th
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              操作者
            </th>
            <th
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              模組
            </th>
            <th
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              事件
            </th>
            <th
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              描述
            </th>
            <th
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              IP 位址
            </th>
            <th
              class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"
            >
              操作
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
            <td class="px-4 py-4 text-sm text-gray-900">
              {{ formatDateTime(log.created_at) }}
            </td>
            <td class="px-4 py-4 text-sm text-gray-900">
              {{ log.causer?.name || log.causer?.email || "系統" }}
            </td>
            <td class="px-4 py-4 text-sm text-gray-500">
              {{ getModuleText(log.log_name || log.module) }}
            </td>
            <td class="px-4 py-4">
              <span :class="getEventClass(log.event)">
                {{ getEventText(log.event) }}
              </span>
            </td>
            <td class="px-4 py-4 text-sm text-gray-700">
              {{ log.description || "-" }}
            </td>
            <td class="px-4 py-4 text-sm text-gray-500">
              {{ log.ip_address || "-" }}
            </td>
            <td class="px-4 py-4 text-sm">
              <button
                @click="handleViewDetail(log.id)"
                class="text-blue-600 hover:text-blue-800"
              >
                詳情
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- 分頁 -->
      <div
        v-if="pagination.last_page > 1"
        class="px-4 py-3 border-t border-gray-200 bg-gray-50"
      >
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            第 {{ pagination.current_page }} 頁，共
            {{ pagination.last_page }} 頁
          </div>
          <div class="flex gap-2">
            <button
              :disabled="pagination.current_page === 1"
              @click="goToPage(pagination.current_page - 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
            >
              上一頁
            </button>
            <button
              :disabled="pagination.current_page === pagination.last_page"
              @click="goToPage(pagination.current_page + 1)"
              class="px-3 py-1 border rounded hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed"
            >
              下一頁
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 詳情 Modal -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto"
      >
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">操作紀錄詳情</h2>
            <button
              @click="closeDetailModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div v-if="currentLog" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-sm text-gray-500">操作時間</div>
                <div class="text-sm font-medium text-gray-900">
                  {{ formatDateTime(currentLog.created_at) }}
                </div>
              </div>
              <div>
                <div class="text-sm text-gray-500">操作者</div>
                <div class="text-sm font-medium text-gray-900">
                  {{ currentLog.causer?.name || "系統" }}
                </div>
              </div>
              <div>
                <div class="text-sm text-gray-500">模組</div>
                <div class="text-sm font-medium text-gray-900">
                  {{ getModuleText(currentLog.log_name || currentLog.module) }}
                </div>
              </div>
              <div>
                <div class="text-sm text-gray-500">事件類型</div>
                <div>
                  <span :class="getEventClass(currentLog.event)">{{
                    getEventText(currentLog.event)
                  }}</span>
                </div>
              </div>
              <div>
                <div class="text-sm text-gray-500">IP 位址</div>
                <div class="text-sm font-medium text-gray-900">
                  {{ currentLog.ip_address || "-" }}
                </div>
              </div>
              <div>
                <div class="text-sm text-gray-500">User Agent</div>
                <div
                  class="text-sm font-medium text-gray-900 truncate"
                  :title="currentLog.user_agent"
                >
                  {{ currentLog.user_agent || "-" }}
                </div>
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">描述</div>
              <div class="text-sm text-gray-900">
                {{ currentLog.description || "-" }}
              </div>
            </div>

            <div v-if="currentLog.properties">
              <div class="text-sm text-gray-500 mb-1">詳細資料</div>
              <pre
                class="text-xs bg-gray-50 p-3 rounded border overflow-x-auto"
                >{{ JSON.stringify(currentLog.properties, null, 2) }}</pre
              >
            </div>
          </div>

          <div v-else class="text-center text-gray-500 py-8">載入中...</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import LoadingPanel from "@/components/LoadingPanel.vue";
import { useActivityLog } from "../composables/useActivityLog";

const {
  logs,
  currentLog,
  loading,
  pagination,
  stats,
  fetchActivityLogs,
  fetchActivityLog,
  fetchMyActivityLogs,
  fetchActivityLogStats,
  getEventText,
  getEventClass,
  getModuleText,
  formatDateTime,
} = useActivityLog();

// 檢視模式：all (全部) 或 my (我的)
const viewMode = ref("all");

// 篩選條件
const filters = ref({
  search: "",
  event: "",
  module: "",
  start_date: "",
  end_date: "",
});

// 詳情 Modal
const showDetailModal = ref(false);

// 計算其他事件數量
const otherEventsCount = computed(() => {
  const total = stats.value.total || 0;
  const created = stats.value.byEvent?.created || 0;
  const updated = stats.value.byEvent?.updated || 0;
  const deleted = stats.value.byEvent?.deleted || 0;
  return Math.max(0, total - created - updated - deleted);
});

// 載入操作紀錄
const loadActivityLogs = async (page = 1) => {
  const params = {
    ...filters.value,
    page,
    per_page: 20,
  };

  if (viewMode.value === "my") {
    await fetchMyActivityLogs(params);
  } else {
    await fetchActivityLogs(params);
  }
};

// 載入統計資料
const loadStats = async () => {
  await fetchActivityLogStats({
    group_by: "event",
    start_date: filters.value.start_date,
    end_date: filters.value.end_date,
  });
};

// 防抖搜尋
let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadActivityLogs();
  }, 300);
};

// 重置篩選
const resetFilters = () => {
  filters.value = {
    search: "",
    event: "",
    module: "",
    start_date: "",
    end_date: "",
  };
  loadActivityLogs();
  loadStats();
};

// 重新整理
const handleRefresh = () => {
  loadActivityLogs();
  loadStats();
};

// 切換檢視模式
const switchToMyLogs = () => {
  viewMode.value = "my";
  loadActivityLogs();
};

const switchToAllLogs = () => {
  viewMode.value = "all";
  loadActivityLogs();
};

// 分頁
const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadActivityLogs(page);
  }
};

// 查看詳情
const handleViewDetail = async (id) => {
  await fetchActivityLog(id);
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
};

onMounted(async () => {
  await loadActivityLogs();
  await loadStats();
});
</script>
