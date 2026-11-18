<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getArticles, addArticle } from '@/api/article'
import { useAuth, fetchMe } from '@/stores/auth'
import ArticleCard from '@/components/ArticleCard.vue'
import Pager from '@/components/Pager.vue'

const router = useRouter()
const { user } = useAuth()

const list  = ref([])
const total = ref(0)
const page  = ref(1)
const pages = ref(1)
const error = ref('')

const showWrite = ref(false)
const title     = ref('')
const content   = ref('')
const submitting = ref(false)
const notice    = ref('')

async function load(p = 1) {
  error.value = ''
  try {
    const data = await getArticles({ page: p })
    list.value  = data.list
    total.value = data.total
    page.value  = data.page
    pages.value = Math.ceil(data.total / data.size) || 1
  } catch (e) {
    error.value = e.message
  }
}

async function submitArticle() {
  notice.value = ''
  if (!title.value.trim()) { error.value = '标题不能为空'; return }
  if (!content.value.trim()) { error.value = '正文不能为空'; return }

  submitting.value = true
  try {
    await addArticle({ title: title.value.trim(), content: content.value.trim() })
    title.value = ''
    content.value = ''
    showWrite.value = false
    notice.value = '已提交，等待管理员审核'
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}

function goWrite() {
  if (!user.value) {
    router.push({ path: '/login', query: { redirect: '/' } })
    return
  }
  showWrite.value = !showWrite.value
}

onMounted(async () => {
  await fetchMe()
  load(1)
})
</script>

<template>
  <div class="list-head">
    <h2>全部文章（共 {{ total }} 篇）</h2>
    <button class="btn primary" @click="goWrite">{{ showWrite ? '收起' : '+ 写文章' }}</button>
  </div>

  <p v-if="notice" class="notice">{{ notice }}</p>
  <p v-if="error" class="error">{{ error }}</p>

  <div v-if="showWrite" class="card">
    <h3>写文章</h3>
    <form class="write-form" @submit.prevent="submitArticle">
      <input v-model="title" type="text" maxlength="200" placeholder="标题">
      <textarea v-model="content" rows="8" placeholder="正文…"></textarea>
      <div>
        <button class="btn primary" type="submit" :disabled="submitting">
          {{ submitting ? '提交中…' : '提交（需审核）' }}
        </button>
      </div>
    </form>
  </div>

  <p v-if="list.length === 0 && !error" class="muted">还没有已发布的文章。</p>

  <ArticleCard v-for="a in list" :key="a.id" :article="a" />

  <Pager :page="page" :pages="pages" @change="load" />
</template>