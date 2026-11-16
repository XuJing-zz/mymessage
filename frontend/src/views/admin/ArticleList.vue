<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { adminArticles } from '@/api/admin'
import StatusTag from '@/components/StatusTag.vue'
import Pager from '@/components/Pager.vue'

const router = useRouter()

const list   = ref([])
const total  = ref(0)
const page   = ref(1)
const pages  = ref(1)
const status = ref('')      // '' 全部 / 0 待审核 / 1 已通过 / 2 已驳回
const error  = ref('')

const TABS = [
  { v: '',  t: '全部' },
  { v: '0', t: '待审核' },
  { v: '1', t: '已通过' },
  { v: '2', t: '已驳回' },
]

async function load(p = 1) {
  error.value = ''
  try {
    const data = await adminArticles({
      page: p,
      size: 10,
      status: status.value === '' ? undefined : status.value,
    })
    list.value  = data.list
    total.value = data.total
    page.value  = data.page
    pages.value = Math.ceil(data.total / data.size) || 1
  } catch (e) {
    error.value = e.message
  }
}

function switchTab(v) {
  status.value = v
  load(1)
}

onMounted(() => load(1))
</script>

<template>
  <div class="card">
    <div class="list-head">
      <h2>文章管理（{{ total }}）</h2>
    </div>

    <div class="tabs">
      <a v-for="t in TABS" :key="t.v" class="tab" :class="{ on: status === t.v }" @click="switchTab(t.v)">{{ t.t }}</a>
    </div>

    <p v-if="error" class="error">{{ error }}</p>

    <table class="admin-table">
      <thead>
        <tr>
          <th width="50">ID</th>
          <th>标题</th>
          <th width="90">状态</th>
          <th width="100">作者</th>
          <th width="60">赞</th>
          <th width="60">评论</th>
          <th width="160">时间</th>
          <th width="70">操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="list.length === 0">
          <td colspan="8" class="muted">没有数据</td>
        </tr>
        <tr v-for="a in list" :key="a.id">
          <td>{{ a.id }}</td>
          <td>{{ a.title }}</td>
          <td><StatusTag :status="a.status" /></td>
          <td>{{ a.username }}</td>
          <td>{{ a.like_count }}</td>
          <td>{{ a.comment_count }}</td>
          <td>{{ a.created_at }}</td>
          <td><a class="del" @click="router.push('/admin/articles/' + a.id)">处理</a></td>
        </tr>
      </tbody>
    </table>

    <Pager :page="page" :pages="pages" @change="load" />
  </div>
</template>