<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { register } from '@/stores/auth'

const router = useRouter()

const username = ref('')
const password = ref('')
const password2 = ref('')
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  if (username.value.trim().length < 3) { error.value = '用户名至少 3 个字符'; return }
  if (password.value.length < 6) { error.value = '密码至少 6 位'; return }
  if (password.value !== password2.value) { error.value = '两次密码不一致'; return }

  loading.value = true
  try {
    await register(username.value.trim(), password.value, password2.value)
    router.replace('/')
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="wrap">
    <div class="card auth-card" style="margin-top: 60px">
      <h2>注册</h2>
      <p v-if="error" class="error">{{ error }}</p>

      <form @submit.prevent="submit">
        <label>用户名（3-20 字）
          <input v-model="username" type="text" maxlength="20" autocomplete="username">
        </label>
        <label>密码（至少 6 位）
          <input v-model="password" type="password" autocomplete="new-password">
        </label>
        <label>确认密码
          <input v-model="password2" type="password" autocomplete="new-password">
        </label>
        <button class="btn primary" type="submit" :disabled="loading">
          {{ loading ? '提交中…' : '注册' }}
        </button>
      </form>

      <p class="muted">已有账号？<router-link to="/login">去登录</router-link></p>
    </div>
  </div>
</template>