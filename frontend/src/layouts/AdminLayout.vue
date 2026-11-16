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
    <div class="wrap-wide topbar-inner">
      <router-link class="logo" to="/admin/articles">🛠 管理后台</router-link>
      <nav>
        <router-link class="btn ghost" to="/admin/articles">文章管理</router-link>
        <router-link class="btn ghost" to="/admin/comments">留言管理</router-link>
        <router-link class="btn ghost" to="/">前台</router-link>
        <span class="user">👤 {{ user?.username }}</span>
        <a class="btn ghost" @click="handleLogout">退出</a>
      </nav>
    </div>
  </header>

  <main class="wrap-wide">
    <router-view />
  </main>
</template>