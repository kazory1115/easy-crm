<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Easy CRM</h1>
        <p class="mt-2 text-sm text-gray-600">登入您的帳戶</p>
      </div>
      <form class="space-y-6" @submit.prevent="handleLogin">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">電子郵件</label>
          <div class="mt-1">
            <input id="email" v-model="email" name="email" type="email" autocomplete="email" required
                   class="block w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm appearance-none focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">密碼</label>
          <div class="mt-1">
            <input id="password" v-model="password" name="password" type="password" autocomplete="current-password" required
                   class="block w-full px-3 py-2 placeholder-gray-400 border border-gray-300 rounded-md shadow-sm appearance-none focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input id="remember-me" name="remember-me" type="checkbox"
                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <label for="remember-me" class="block ml-2 text-sm text-gray-900">記住我</label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">忘記密碼？</a>
          </div>
        </div>

        <div>
          <button type="submit"
                  class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            登入
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const email = ref('test@example.com');
const password = ref('password');
const authStore = useAuthStore();
const router = useRouter();

const handleLogin = async () => {
  try {
    // This will be a mocked call for now
    await authStore.login({ email: email.value, password: password.value });
    // Redirect to a protected page, e.g., home or dashboard
    router.push('/');
  } catch (error) {
    console.error('Login failed:', error);
    // Here you could show an error message to the user
  }
};
</script>
