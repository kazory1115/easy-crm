<template>
  <div class="p-8">
    <div class="max-w-2xl p-8 mx-auto bg-white rounded-lg shadow-md">
      <h1 class="mb-6 text-2xl font-bold text-gray-800">使用者個人資料</h1>
      <div v-if="user" class="space-y-4">
        <div>
          <p class="text-sm font-medium text-gray-500">姓名</p>
          <p class="text-lg text-gray-900">{{ user.name }}</p>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">電子郵件</p>
          <p class="text-lg text-gray-900">{{ user.email }}</p>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">角色</p>
          <div class="flex flex-wrap gap-2 mt-1">
            <span v-for="role in user.roles" :key="role"
                  class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
              {{ role }}
            </span>
          </div>
        </div>
        <div class="pt-4">
          <button @click="handleLogout"
                  class="px-4 py-2 font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            登出
          </button>
        </div>
      </div>
      <div v-else>
        <p>正在載入使用者資料...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const user = computed(() => authStore.user);

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
