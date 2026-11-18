<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminArticle, auditArticle, updateArticle, adminDeleteComment } from '@/api/admin'
import { deleteArticle } from '@/api/article'
import StatusTag from '@/components/StatusTag.vue'

const route  = useRoute()
const router = useRouter()

const article  = ref(null)
const comments = ref([])
const error    = ref('')
const loading  = ref(true)

// 编辑状态
const editing     = ref(false)
const editTitle   = ref('')
const editContent = ref('')

function startEdit() {
  editTitle.value   = article.value.title
  editContent.value = article.value.content
  editing.value     = true
}

function cancelEdit() {
  editing.value = false
}

async function saveEdit() {
  const t = editTitle.value.trim()
  const c = editContent.value.trim()
  if (t === '') { alert('标题不能为空'); return }
  if (c === '') { alert('内容不能为空'); return }

  try {
    await updateArticle({ id: article.value.id, title: t, content: c })
    editing.value = false
    await load()
  } catch (e) {
    alert(e.message)
  }
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const d = await adminArticle(route.params.id)
    article.value  = d.article
    comments.value = d.comments
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

async function audit(status) {
  const word = status === 1 ? '通过' : '驳回'
  if (!confirm(`确定${word}这篇文章？`)) return
  try {
    await auditArticle(article.value.id, status)
    await load()
  } catch (e) { alert(e.message) }
}

async function removeArticle() {
  if (!confirm('确定删除这篇文章？')) return
  try {
    await deleteArticle(article.value.id)
    router.replace('/admin/articles')
  } catch (e) { alert(e.message) }
}

async function removeComment(id) {
  if (!confirm('确定删除这条评论？')) return
  try {
    await adminDeleteComment(id)
    await load()
  } catch (e) { alert(e.message) }
}

onMounted(load)
</script>

<template>
  <p v-if="loading" class="muted">加载中…</p>
  <p v-else-if="error" class="error">{{ error }}</p>

  <template v-else-if="article">
    <div class="card">
      <div class="list-head">
        <h2>{{ editing ? '编辑文章' : article.title }}</h2>
        <StatusTag :status="article.status" />
      </div>
      <p class="muted">{{ article.username }} · {{ article.created_at }} · ♡ {{ article.like_count }} · 💬 {{ article.comment_count }}</p>

      <!-- 编辑模式 -->
      <template v-if="editing">
        <label>标题
          <input type="text" v-model="editTitle" maxlength="200">
        </label>
        <label>正文
          <textarea v-model="editContent" rows="12"></textarea>
        </label>
      </template>

      <!-- 查看模式 -->
      <div v-else class="art-content" style="white-space: pre-wrap">{{ article.content }}</div>

      <div class="msg-foot">
        <template v-if="editing">
          <button class="btn primary" @click="saveEdit">保存修改</button>
          <button class="btn ghost" @click="cancelEdit">取消</button>
        </template>
        <template v-else>
          <template v-if="article.status === 0">
            <button class="btn primary" @click="audit(1)">通过</button>
            <button class="btn ghost" @click="audit(2)">驳回</button>
          </template>
          <button class="btn ghost" @click="startEdit">编辑</button>
          <button class="btn ghost" @click="removeArticle">删除文章</button>
          <router-link class="del" to="/admin/articles">← 返回列表</router-link>
        </template>
      </div>
    </div>

    <div class="card">
      <h3>评论（{{ comments.length }}）</h3>
      <p v-if="comments.length === 0" class="muted">还没有评论。</p>
      <div class="comment" v-for="c in comments" :key="c.id">
        <div class="msg-head">
          <strong>{{ c.username }}</strong>
          <time>{{ c.created_at }}</time>
          <a class="del" @click="removeComment(c.id)">删除</a>
        </div>
        <p style="white-space: pre-wrap">{{ c.content }}</p>
      </div>
    </div>
  </template>
</template>