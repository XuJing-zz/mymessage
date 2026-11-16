<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { login } from '@/stores/auth'

const router = useRouter()
const route = useRoute()

const username = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  if (!username.value.trim()) { error.value = '请输入用户名'; return }
  if (!password.value) { error.value = '请输入密码'; return }

  loading.value = true
  try {
    const u = await login(username.value.trim(), password.value)
    const redirect = route.query.redirect
    if (redirect) {
      router.replace(redirect)
    } else {
      router.replace(u.role === 1 ? '/admin/articles' : '/')
    }
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="wrap">
    <div class="card auth-card" style="margin-top: 80px">
      <h2>登录</h2>
      <p v-if="error" class="error">{{ error }}</p>

      <form @submit.prevent="submit">
        <label>用户名
          <input v-model="username" type="text" autocomplete="username">
        </label>
        <label>密码
          <input v-model="password" type="password" autocomplete="current-password">
        </label>
        <button class="btn primary" type="submit" :disabled="loading">
          {{ loading ? '登录中…' : '登录' }}
        </button>
      </form>

      <p class="muted">没有账号？<router-link to="/register">去注册</router-link></p>
      <p class="muted"><router-link to="/">← 回首页</router-link></p>
    </div>
  </div>
</template>