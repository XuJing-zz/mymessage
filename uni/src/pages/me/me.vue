<template>
  <view class="page">
    <view v-if="!user" class="card center">
      <text class="muted">还没有登录</text>
      <button class="submit" @click="goLogin">去登录</button>
    </view>

    <template v-else>
      <view class="card user-card">
        <text class="name">{{ user.username }}</text>
        <text class="role">{{ user.role === 1 ? '管理员' : '普通用户' }}</text>
        <text class="logout" @click="doLogout">退出登录</text>
      </view>

      <view class="tabs">
        <text class="tab" :class="{ active: tab === 'articles' }" @click="switchTab('articles')">我的文章</text>
        <text class="tab" :class="{ active: tab === 'comments' }" @click="switchTab('comments')">我的评论</text>
        <text class="tab" :class="{ active: tab === 'likes' }" @click="switchTab('likes')">我的点赞</text>
      </view>

      <view v-if="error" class="error">{{ error }}</view>
      <view v-if="list.length === 0" class="empty">暂无数据</view>

      <view class="card item" v-for="it in list" :key="it.id">
        <template v-if="tab === 'articles'">
          <text class="item-title" @click="goDetail(it.id)">{{ it.title }}</text>
          <view class="item-meta">
            <text class="tag">{{ it.status_text }}</text>
            <text class="muted">♡ {{ it.like_count }} · 💬 {{ it.comment_count }}</text>
          </view>
        </template>

        <template v-else-if="tab === 'comments'">
          <text class="item-title" @click="goDetail(it.article_id)">{{ it.article_title }}</text>
          <text class="item-body">{{ it.content }}</text>
          <text class="muted">{{ it.created_at }}</text>
        </template>

        <template v-else>
          <text class="item-title" @click="goDetail(it.id)">{{ it.title }}</text>
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
import { myArticles, myComments, myLikes, logout } from '@/api/index'
import TabBar from '@/components/TabBar.vue'

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

function goDetail(id) {
  uni.navigateTo({ url: '/pages/detail/detail?id=' + id })
}

function goLogin() {
  uni.navigateTo({ url: '/pages/login/login' })
}

async function doLogout() {
  try { await logout() } catch (e) {}
  uni.removeStorageSync('user')
  user.value = null
  uni.showToast({ title: '已退出', icon: 'none' })
}
</script>

<style scoped>
.page { min-height: 100vh; padding: 24rpx 24rpx calc(140rpx + env(safe-area-inset-bottom)); }
.card { background: #fff; border-radius: 20rpx; padding: 26rpx; margin-bottom: 20rpx; }
.center { display: flex; flex-direction: column; align-items: center; gap: 24rpx; padding: 60rpx 26rpx; }
.muted { font-size: 24rpx; color: #8a8f99; }

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
.empty { text-align: center; color: #8a8f99; font-size: 26rpx; padding: 40rpx 0; }
.error { color: #e5484d; font-size: 24rpx; margin-bottom: 16rpx; }
.submit { background: #2b7fff; color: #fff; font-size: 28rpx; border-radius: 999rpx; }
</style>