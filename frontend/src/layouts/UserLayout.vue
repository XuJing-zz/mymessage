<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth, fetchMe, logout } from '@/stores/auth'

const router = useRouter()
const { user } = useAuth()

async function handleLogout() {
  await logout()
  router.replace('/login')
}

onMounted(() => fetchMe())
</script>

<template>
  <header class="topbar">
    <div class="wrap topbar-inner">
      <router-link class="logo" to="/">📄 我的博客</router-link>
      <nav>
        <router-link class="btn ghost" to="/">文章</router-link>
        <router-link v-if="user" class="btn ghost" to="/me">我的</router-link>
        <router-link v-if="user?.role === 1" class="btn ghost" to="/admin/articles">管理后台</router-link>

        <template v-if="user">
          <span class="user">👤 {{ user.username }}</span>
          <a class="btn ghost" @click="handleLogout">退出</a>
        </template>
        <template v-else>
          <router-link class="btn ghost" to="/login">登录</router-link>
          <router-link class="btn primary" to="/register">注册</router-link>
        </template>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <router-view />
  </main>
</template>