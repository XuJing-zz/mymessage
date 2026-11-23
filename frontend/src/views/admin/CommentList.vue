<script setup>
import { ref, onMounted } from 'vue'
import { adminComments, adminDeleteComment } from '@/api/admin'
import Pager from '@/components/Pager.vue'

const list  = ref([])
const total = ref(0)
const page  = ref(1)
const pages = ref(1)
const keyword = ref('')
const error = ref('')
const loading = ref(false)

async function load(p = 1) {
  error.value = ''
  loading.value = true
  try {
    const data = await adminComments({
      page: p,
      size: 20,
      keyword: keyword.value.trim() || undefined,
    })
    list.value  = data.list
    total.value = data.total
    page.value  = data.page
    pages.value = Math.ceil(data.total / data.size) || 1
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

async function remove(id) {
  if (!confirm('确定删除这条留言？')) return
  try {
    await adminDeleteComment(id)
    await load(page.value)
  } catch (e) {
    alert(e.message)
  }
}

onMounted(() => load(1))
</script>

<template>
  <div class="card">
    <div class="list-head">
      <h2>留言管理（{{ total }}）</h2>
    </div>

    <form class="toolbar" @submit.prevent="load(1)">
      <input type="text" v-model="keyword" placeholder="搜索内容 / 用户名 / 文章标题">
      <button class="btn primary" type="submit">搜索</button>
      <button class="btn ghost" type="button" @click="keyword = ''; load(1)">清空</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="loading" class="muted">加载中…</p>

    <table class="admin-table">
      <thead>
        <tr>
          <th width="50">ID</th>
          <th width="220">文章</th>
          <th width="100">用户</th>
          <th>内容</th>
          <th width="150">时间</th>
          <th width="70">操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="list.length === 0">
          <td colspan="6" class="muted">还没有留言</td>
        </tr>
        <tr v-for="c in list" :key="c.id">
          <td>{{ c.id }}</td>
          <td>
            <router-link :to="'/admin/articles/' + c.article_id">{{ c.article_title }}</router-link>
          </td>
          <td>{{ c.username }}</td>
          <td>{{ c.content }}</td>
          <td>{{ c.created_at }}</td>
          <td><a class="del" @click="remove(c.id)">删除</a></td>
        </tr>
      </tbody>
    </table>

    <Pager :page="page" :pages="pages" @change="load" />
  </div>
</template>
