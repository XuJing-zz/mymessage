<script setup>
import { ref, onMounted } from 'vue'
import { myArticles, myComments, myLikes } from '@/api/me'
import { deleteArticle } from '@/api/article'
import { deleteComment } from '@/api/comment'
import StatusTag from '@/components/StatusTag.vue'

const tab = ref('articles')

const articles = ref([])
const comments = ref([])
const likes    = ref([])
const error    = ref('')
const loading  = ref(false)

async function loadArticles() {
  const d = await myArticles({ page: 1, size: 50 })
  articles.value = d.list
}
async function loadComments() {
  const d = await myComments({ page: 1, size: 50 })
  comments.value = d.list
}
async function loadLikes() {
  const d = await myLikes({ page: 1, size: 50 })
  likes.value = d.list
}

async function switchTab(t) {
  tab.value = t
  error.value = ''
  loading.value = true
  try {
    if (t === 'articles') await loadArticles()
    if (t === 'comments') await loadComments()
    if (t === 'likes')    await loadLikes()
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

async function removeArticle(id) {
  if (!confirm('确定删除这篇文章？')) return
  try {
    await deleteArticle(id)
    await loadArticles()
  } catch (e) { alert(e.message) }
}

async function removeComment(id) {
  if (!confirm('确定删除这条评论？')) return
  try {
    await deleteComment(id)
    await loadComments()
  } catch (e) { alert(e.message) }
}

onMounted(() => switchTab('articles'))
</script>

<template>
  <h2>我的首页</h2>

  <div class="tabs">
    <a class="tab" :class="{ on: tab === 'articles' }" @click="switchTab('articles')">我的文章（{{ articles.length }}）</a>
    <a class="tab" :class="{ on: tab === 'comments' }" @click="switchTab('comments')">我的评论（{{ comments.length }}）</a>
    <a class="tab" :class="{ on: tab === 'likes' }"    @click="switchTab('likes')">我的点赞（{{ likes.length }}）</a>
  </div>

  <p v-if="error" class="error">{{ error }}</p>
  <p v-if="loading" class="muted">加载中…</p>

  <!-- 我的文章 -->
  <template v-if="tab === 'articles'">
    <p v-if="articles.length === 0" class="muted">还没有发过文章。</p>
    <div class="card" v-for="a in articles" :key="a.id">
      <div class="list-head">
        <h3 class="art-title">
          <router-link :to="'/article/' + a.id">{{ a.title }}</router-link>
        </h3>
        <StatusTag :status="a.status" />
      </div>
      <p class="muted">{{ a.created_at }} · ♡ {{ a.like_count }} · 💬 {{ a.comment_count }}</p>
      <div class="msg-foot">
        <router-link class="del" :to="'/article/' + a.id">查看</router-link>
        <a class="del" @click="removeArticle(a.id)">删除</a>
      </div>
    </div>
  </template>

  <!-- 我的评论 -->
  <template v-if="tab === 'comments'">
    <p v-if="comments.length === 0" class="muted">还没有评论。</p>
    <div class="card" v-for="c in comments" :key="c.id">
      <p class="muted">在《{{ c.article_title }}》下 · {{ c.created_at }}</p>
      <p style="white-space: pre-wrap">{{ c.content }}</p>
      <div class="msg-foot">
        <router-link class="del" :to="'/article/' + c.article_id">去看原文</router-link>
        <a class="del" @click="removeComment(c.id)">删除</a>
      </div>
    </div>
  </template>

  <!-- 我的点赞 -->
  <template v-if="tab === 'likes'">
    <p v-if="likes.length === 0" class="muted">还没有点过赞。</p>
    <div class="card" v-for="a in likes" :key="a.id">
      <h3 class="art-title">
        <router-link :to="'/article/' + a.id">{{ a.title }}</router-link>
      </h3>
      <p class="muted">{{ a.username }} · {{ a.created_at }} · ♡ {{ a.like_count }} · 💬 {{ a.comment_count }}</p>
    </div>
  </template>
</template>