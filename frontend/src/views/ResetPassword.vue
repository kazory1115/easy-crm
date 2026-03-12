<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-md">
      <h1 class="text-2xl font-bold text-gray-900">重設密碼</h1>
      <p class="mt-2 text-sm text-gray-600">請輸入新密碼，送出後即可使用新密碼登入。</p>

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

        <div>
          <label for="password" class="mb-1 block text-sm font-medium text-gray-700">新密碼</label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            minlength="8"
            class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
          >
        </div>

        <div>
          <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">確認新密碼</label>
          <input
            id="password_confirmation"
            v-model="passwordConfirmation"
            type="password"
            required
            minlength="8"
            class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
          >
        </div>

        <button
          type="submit"
          :disabled="loading || !token"
          class="flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ loading ? '送出中...' : '重設密碼' }}
        </button>

        <p v-if="!token" class="text-sm text-rose-600">缺少重設 token，請重新申請重設密碼信。</p>
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
import { computed, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { post } from '@/utils/http'

const route = useRoute()
const router = useRouter()

const email = ref(String(route.query.email || ''))
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const token = computed(() => String(route.query.token || ''))

async function handleSubmit() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await post('/auth/reset-password', {
      email: email.value,
      token: token.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    successMessage.value = response?.message || '密碼已重設成功，請重新登入。'

    setTimeout(() => {
      router.push('/login')
    }, 1200)
  } catch (error) {
    errorMessage.value = error?.response?.data?.message
      || Object.values(error?.response?.data?.errors || {})?.[0]?.[0]
      || '重設密碼失敗'
  } finally {
    loading.value = false
  }
}
</script>
