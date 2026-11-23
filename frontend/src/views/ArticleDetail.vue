<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getArticleDetail } from '@/api/article'
import { addComment, deleteComment } from '@/api/comment'
import { like, unlike } from '@/api/like'
import { deleteArticle } from '@/api/article'
import { useAuth, fetchMe } from '@/stores/auth'
import CommentList from '@/components/CommentList.vue'

const route = useRoute()
const router = useRouter()
const { user } = useAuth()

const article  = ref(null)
const comments = ref([])
const text     = ref('')
const error    = ref('')
const loading  = ref(true)
const busy     = ref(false)

const id = computed(() => Number(route.params.id))
const isAdmin = computed(() => user.value?.role === 1)
const canDeleteArticle = computed(() => {
  if (!user.value || !article.value) return false
  return isAdmin.value || article.value.username === user.value.username
})
function canDeleteComment(c) {
  if (!user.value) return false
  if (user.value.role === 1) return true
  return Number(c.user_id) === Number(user.value.id)
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const data = await getArticleDetail(id.value)
    article.value  = data.article
    comments.value = data.comments
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

async function toggleLike() {
  if (!user.value) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }
  try {
    const r = article.value.liked
      ? await unlike(id.value)
      : await like(id.value)
    article.value.liked = r.liked
    article.value.like_count = r.like_count
  } catch (e) {
    alert(e.message)
  }
}

async function submitComment() {
  if (!user.value) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }
  if (!text.value.trim()) { alert('评论不能为空'); return }

  busy.value = true
  try {
    await addComment(id.value, text.value.trim())
    text.value = ''
    const data = await getArticleDetail(id.value)
    comments.value = data.comments
    article.value.comment_count = data.comments.length
  } catch (e) {
    alert(e.message)
  } finally {
    busy.value = false
  }
}

async function removeComment(cid) {
  if (!confirm('确定删除这条评论？')) return
  try {
    await deleteComment(cid)
    const data = await getArticleDetail(id.value)
    comments.value = data.comments
    article.value.comment_count = data.comments.length
  } catch (e) {
    alert(e.message)
  }
}

async function removeArticle() {
  if (!confirm('确定删除这篇文章？')) return
  try {
    await deleteArticle(id.value)
    router.replace('/')
  } catch (e) {
    alert(e.message)
  }
}

onMounted(async () => {
  await fetchMe()
  load()
})

watch(() => route.params.id, () => load())
</script>

<template>
  <p v-if="loading" class="muted">加载中…</p>
  <p v-else-if="error" class="error">{{ error }}</p>

  <template v-else-if="article">
    <article class="card">
      <h2 class="art-title">{{ article.title }}</h2>
      <p class="muted">{{ article.username }} · {{ article.created_at }}</p>

      <div class="art-content" style="white-space: pre-wrap">{{ article.content }}</div>

      <div class="msg-foot">
        <button class="like-btn" :class="{ liked: article.liked }" @click="toggleLike">
          {{ article.liked ? '♥' : '♡' }} {{ article.like_count }}
        </button>
        <button v-if="canDeleteArticle" class="del" @click="removeArticle">删除文章</button>
      </div>
    </article>

    <section class="card">
      <h3>评论（{{ comments.length }}）</h3>

      <form v-if="user" class="write-form" @submit.prevent="submitComment">
        <textarea v-model="text" rows="3" maxlength="500" placeholder="写下你的评论…"></textarea>
        <button class="btn primary" type="submit" :disabled="busy">
          {{ busy ? '提交中…' : '发表评论' }}
        </button>
      </form>
      <p v-else class="muted">
        <a class="link" @click="router.push({ path: '/login', query: { redirect: route.fullPath } })">登录</a> 后才能评论。
      </p>

      <CommentList :comments="comments" :can-delete="canDeleteComment" @delete="removeComment" />
    </section>
  </template>
</template>