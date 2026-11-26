<template>
  <view class="page">
    <view v-if="!user" class="card center">
      <text class="muted">还没有登录</text>
      <navigator class="submit" url="/pages/login/login" hover-class="none">去登录</navigator>
    </view>

    <template v-else>
      <view class="card user-card">
        <text class="name">{{ user.username }}</text>
        <text class="role">{{ user.role === 1 ? '管理员' : '普通用户' }}</text>
        <text class="logout" @click="doLogout">退出登录</text>
      </view>

      <navigator v-if="user.role === 1" class="card admin-entry" url="/pages/admin/list" hover-class="none">
        <text class="admin-title">🛠 管理后台</text>
        <text class="muted">审核文章 / 留言 / 用户</text>
      </navigator>

      <view class="tabs">
        <text class="tab" :class="{ active: tab === 'articles' }" @click="switchTab('articles')">我的文章</text>
        <text class="tab" :class="{ active: tab === 'comments' }" @click="switchTab('comments')">我的评论</text>
        <text class="tab" :class="{ active: tab === 'likes' }" @click="switchTab('likes')">我的点赞</text>
      </view>

      <view v-if="error" class="error">{{ error }}</view>
      <view v-if="list.length === 0" class="empty">暂无数据</view>

      <view class="card item" v-for="it in list" :key="it.id">
        <template v-if="tab === 'articles'">
          <navigator class="item-title" :url="'/pages/detail/detail?id=' + it.id" hover-class="none">{{ it.title }}</navigator>
          <view class="item-meta">
            <text class="tag">{{ it.status_text }}</text>
            <text class="muted">♡ {{ it.like_count }} · 💬 {{ it.comment_count }}</text>
            <text class="del" @click.stop="removeArticle(it.id)">删除</text>
          </view>
        </template>

        <template v-else-if="tab === 'comments'">
          <navigator class="item-title" :url="'/pages/detail/detail?id=' + it.article_id" hover-class="none">{{ it.article_title }}</navigator>
          <text class="item-body">{{ it.content }}</text>
          <text class="muted">{{ it.created_at }}</text>
        </template>

        <template v-else>
          <navigator class="item-title" :url="'/pages/detail/detail?id=' + it.id" hover-class="none">{{ it.title }}</navigator>
          <text class="muted">{{ it.username }} · ♡ {{ it.like_count }}</text>
        </template>
      </view>
    </template>

    <TabBar :current="2" />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { myArticles, myComments, myLikes, logout, deleteArticle } from '../../service/index'
import TabBar from '../../components/TabBar.vue'

const user  = ref(null)
const tab   = ref('articles')
const list  = ref([])
const error = ref('')

async function load() {
  error.value = ''
  if (!user.value) return
  try {
    let d
    if (tab.value === 'articles')      d = await myArticles()
    else if (tab.value === 'comments') d = await myComments()
    else                               d = await myLikes()
    list.value = d.list
  } catch (e) {
    error.value = e.message
    list.value = []
  }
}

function switchTab(t) {
  tab.value = t
  load()
}

onShow(() => {
  user.value = uni.getStorageSync('user') || null
  load()
})

function removeArticle(id) {
  uni.showModal({
    title: '删除文章',
    content: '删除后不可恢复，确定删除？',
    success: async (res) => {
      if (!res.confirm) return
      try {
        await deleteArticle(id)
        uni.showToast({ title: '已删除', icon: 'none' })
        await load()
      } catch (e) {
        uni.showToast({ title: e.message, icon: 'none' })
      }
    },
  })
}

async function doLogout() {
  try { await logout() } catch (e) {}
  uni.removeStorageSync('user')
  uni.removeStorageSync('token')
  user.value = null
  uni.showToast({ title: '已退出', icon: 'none' })
}
</script>

<style scoped>
.page { width: 100%; overflow-x: hidden; box-sizing: border-box; min-height: 100vh; padding: 24rpx 24rpx calc(140rpx + env(safe-area-inset-bottom)); }
.card {
  width: 100%;
  box-sizing: border-box;
  background: #fff; border-radius: 20rpx; padding: 26rpx; margin-bottom: 20rpx;
}
.center { display: flex; flex-direction: column; align-items: center; gap: 24rpx; padding: 60rpx 26rpx; }
.muted { font-size: 24rpx; color: #8a8f99; }
.item { display: block; }
.none { display: none; }

.admin-entry { display: flex; align-items: center; justify-content: space-between; }
.admin-title { font-size: 30rpx; font-weight: 600; color: #1f2329; }
.user-card { display: flex; align-items: center; gap: 16rpx; }
.name { font-size: 34rpx; font-weight: 700; color: #1f2329; }
.role { font-size: 22rpx; color: #2b7fff; background: #eef4ff; padding: 4rpx 14rpx; border-radius: 999rpx; }
.logout { margin-left: auto; font-size: 24rpx; color: #e5484d; }

.tabs { display: flex; gap: 36rpx; padding: 10rpx 8rpx 20rpx; }
.tab { font-size: 28rpx; color: #8a8f99; }
.tab.active { color: #1f2329; font-weight: 700; border-bottom: 4rpx solid #2b7fff; padding-bottom: 8rpx; }

.item-title { display: block; font-size: 30rpx; color: #1f2329; font-weight: 600; margin-bottom: 10rpx; }
.item-body { display: block; font-size: 26rpx; color: #5c6370; margin-bottom: 10rpx; }
.item-meta { display: flex; align-items: center; gap: 16rpx; }
.tag { font-size: 22rpx; color: #2b7fff; background: #eef4ff; padding: 4rpx 12rpx; border-radius: 8rpx; }
.del { margin-left: auto; font-size: 24rpx; color: #e5484d; }
.empty { text-align: center; color: #8a8f99; font-size: 26rpx; padding: 40rpx 0; }
.error { color: #e5484d; font-size: 24rpx; margin-bottom: 16rpx; }
navigator.submit { padding: 16rpx 56rpx; text-align: center; line-height: 1.4; }
.submit { background: #2b7fff; color: #fff; font-size: 28rpx; border-radius: 999rpx; }
</style>
