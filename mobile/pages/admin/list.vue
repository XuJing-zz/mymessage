<template>
  <view class="page">
    <view class="nav-links">
      <text class="nav-back" @click="goBack">‹ 首页</text>
      <text class="nav-link active">文章审核</text>
      <navigator class="nav-link" url="/pages/admin/comments" hover-class="none">留言管理</navigator>
      <navigator class="nav-link" url="/pages/admin/users" hover-class="none">用户</navigator>
    </view>

    <view class="tabs">
      <text class="tab" :class="{ active: status === '0' }" @click="switchTab('0')">待审核</text>
      <text class="tab" :class="{ active: status === '1' }" @click="switchTab('1')">已通过</text>
      <text class="tab" :class="{ active: status === '2' }" @click="switchTab('2')">已驳回</text>
    </view>

    <view v-if="error" class="error">{{ error }}</view>
    <view v-if="!loading && list.length === 0" class="empty">没有数据</view>

    <view class="card" v-for="a in list" :key="a.id">
      <text class="title">{{ a.title }}</text>
      <view class="meta">
        <text class="muted">{{ a.username }}</text>
        <text class="muted">{{ a.created_at }}</text>
      </view>

      <!-- 审核必须能看到正文 -->
      <view class="body">
        <text class="content">{{ a.content }}</text>
      </view>

      <view class="meta">
        <text class="tag">{{ a.status_text }}</text>
        <text class="muted">♡ {{ a.like_count }} · 💬 {{ a.comment_count }}</text>
      </view>

      <view class="actions">
        <template v-if="status === '0'">
          <button class="btn pass" size="mini" @click="doAudit(a.id, 1)">通过</button>
          <button class="btn reject" size="mini" @click="doAudit(a.id, 2)">驳回</button>
        </template>
        <button class="btn del" size="mini" @click="doDelete(a)">删除</button>
      </view>
    </view>

    <view class="pager">
      <text class="btn-page" :class="{ disabled: page <= 1 }" @click="go(page - 1)">上一页</text>
      <text class="pageinfo">{{ page }} / {{ pages }}</text>
      <text class="btn-page" :class="{ disabled: page >= pages }" @click="go(page + 1)">下一页</text>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { adminArticles, auditArticle, deleteArticle } from '../../service/index'

function goBack() {
  const ps = getCurrentPages()
  if (ps.length > 1) uni.navigateBack()
  else uni.reLaunch({ url: '/pages/article/list' })
}

const status  = ref('0')
const list    = ref([])
const page    = ref(1)
const pages   = ref(1)
const loading = ref(false)
const error   = ref('')
const opened  = ref({})

function excerpt(t) {
  const s = (t || '').trim()
  return s.length > 120 ? s.slice(0, 120) + '…' : s
}
function needFold(t) {
  return (t || '').trim().length > 120
}
function toggle(id) {
  opened.value = { ...opened.value, [id]: !opened.value[id] }
}

async function load(p = 1) {
  loading.value = true
  error.value = ''
  try {
    const d = await adminArticles(status.value, p)
    list.value = d.list
    page.value = d.page
    pages.value = Math.ceil(d.total / d.size) || 1
  } catch (e) {
    error.value = e.message
    list.value = []
  } finally {
    loading.value = false
  }
}

function switchTab(s) {
  status.value = s
  opened.value = {}
  load(1)
}

function go(p) {
  if (p < 1 || p > pages.value) return
  load(p)
}

async function doAudit(id, s) {
  const word = s === 1 ? '通过' : '驳回'
  uni.showModal({
    title: '确认',
    content: '确定' + word + '这篇文章？',
    success: async (res) => {
      if (!res.confirm) return
      try {
        await auditArticle(id, s)
        uni.showToast({ title: '已' + word, icon: 'none' })
        await load(page.value)
      } catch (e) {
        uni.showToast({ title: e.message, icon: 'none' })
      }
    },
  })
}

function doDelete(a) {
  uni.showModal({
    title: '删除文章',
    content: '将删除《' + a.title + '》及其评论、点赞，不可恢复。确定？',
    success: async (res) => {
      if (!res.confirm) return
      try {
        await deleteArticle(a.id)
        uni.showToast({ title: '已删除', icon: 'none' })
        await load(page.value)
      } catch (e) {
        uni.showToast({ title: e.message, icon: 'none' })
      }
    },
  })
}

onShow(() => {
  const u = uni.getStorageSync('user')
  if (!u || u.role !== 1) {
    uni.showToast({ title: '需要管理员权限', icon: 'none' })
    setTimeout(() => uni.reLaunch({ url: '/pages/article/list' }), 800)
    return
  }
  load(1)
})
</script>

<style scoped>
.page { width: 100%; overflow-x: hidden; box-sizing: border-box; min-height: 100vh; padding: 24rpx 24rpx 60rpx; }
.nav-links { display: flex; gap: 32rpx; padding: 10rpx 8rpx 20rpx; }
.nav-link { font-size: 28rpx; color: #8a8f99; }
.nav-link.active { color: #1f2329; font-weight: 700; border-bottom: 4rpx solid #2b7fff; padding-bottom: 8rpx; }
.tabs { display: flex; gap: 36rpx; padding: 10rpx 8rpx 24rpx; }
.tab { font-size: 28rpx; color: #8a8f99; }
.tab.active { color: #1f2329; font-weight: 700; border-bottom: 4rpx solid #2b7fff; padding-bottom: 8rpx; }

.card {
  width: 100%;
  box-sizing: border-box;
  background: #fff; border-radius: 20rpx; padding: 26rpx; margin-bottom: 20rpx;
}
.title { display: block; font-size: 30rpx; font-weight: 600; color: #1f2329; margin-bottom: 12rpx; }
.meta { display: flex; gap: 20rpx; align-items: center; margin-bottom: 10rpx; }
.muted { font-size: 22rpx; color: #8a8f99; }
.tag { font-size: 22rpx; color: #2b7fff; background: #eef4ff; padding: 4rpx 12rpx; border-radius: 8rpx; }

.body {
  background: #f7f8fa;
  border-radius: 12rpx;
  padding: 18rpx 20rpx;
  margin: 12rpx 0 14rpx;
}
.content { display: block; font-size: 26rpx; color: #333; line-height: 1.7; word-break: break-all; white-space: pre-wrap; }
.fold { display: inline-block; margin-top: 10rpx; font-size: 24rpx; color: #2b7fff; }

.actions { display: flex; gap: 16rpx; margin-top: 18rpx; }
.btn { margin: 0; border-radius: 999rpx; font-size: 24rpx; }
.pass { background: #2b7fff; color: #fff; }
.reject { background: #f4f5f7; color: #5c6370; }
.del { margin-left: auto; background: #fff1f0; color: #e5484d; }

.pager { display: flex; align-items: center; justify-content: center; gap: 32rpx; padding: 20rpx 0; }
.btn-page { font-size: 26rpx; color: #2b7fff; padding: 10rpx 20rpx; }
.btn-page.disabled { color: #c9ced6; }
.pageinfo { font-size: 24rpx; color: #8a8f99; }

.empty { text-align: center; color: #8a8f99; font-size: 26rpx; padding: 60rpx 0; }
.error { color: #e5484d; font-size: 24rpx; margin-bottom: 16rpx; }
.nav-back { font-size: 26rpx; color: #666; padding: 10rpx 16rpx 10rpx 0; }
</style>
