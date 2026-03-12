<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-md">
      <h1 class="text-2xl font-bold text-gray-900">忘記密碼</h1>
      <p class="mt-2 text-sm text-gray-600">輸入登入 Email，系統會寄送重設密碼連結。</p>

      <form class="mt-6 space-y-5" @submit.prevent="handleSubmit">
        <div>
          <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            v-model.trim="email"
            type="email"
            required
            class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
          >
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ loading ? '寄送中...' : '寄送重設信' }}
        </button>

        <p v-if="successMessage" class="text-sm text-emerald-600">{{ successMessage }}</p>
        <p v-if="errorMessage" class="text-sm text-rose-600">{{ errorMessage }}</p>
      </form>

      <div class="mt-6 text-sm">
        <RouterLink to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">返回登入</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { post } from '@/utils/http'

const email = ref('')
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

async function handleSubmit() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await post('/auth/forgot-password', { email: email.value })
    successMessage.value = response?.message || '如果該 Email 存在，系統已寄出重設密碼信。'
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || '寄送重設密碼信失敗'
  } finally {
    loading.value = false
  }
}
</script>
