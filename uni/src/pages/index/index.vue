<template>
  <view class="page">
    <view class="header">
      <text class="h1">最新文章</text>
      <text v-if="user" class="who" @click="goMe">{{ user.username }}</text>
      <text v-else class="who" @click="goLogin">未登录 · 去登录</text>
    </view>

    <view v-if="error" class="error">{{ error }}</view>

    <view v-if="!loading && list.length === 0" class="empty">还没有文章</view>

    <view class="card" v-for="a in list" :key="a.id" @click="goDetail(a.id)">
      <text class="title">{{ a.title }}</text>
      <text class="content">{{ a.content }}</text>
      <view class="meta">
        <text class="meta-item">{{ a.username }}</text>
        <text class="meta-item">♡ {{ a.like_count }}</text>
        <text class="meta-item">💬 {{ a.comment_count }}</text>
      </view>
    </view>

    <view class="pager">
      <text class="btn" :class="{ disabled: page <= 1 }" @click="go(page - 1)">上一页</text>
      <text class="pageinfo">{{ page }} / {{ pages }}</text>
      <text class="btn" :class="{ disabled: page >= pages }" @click="go(page + 1)">下一页</text>
    </view>

    <TabBar :current="0" />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow, onPullDownRefresh } from '@dcloudio/uni-app'
import { getArticles } from '@/api/index'
import TabBar from '@/components/TabBar.vue'

const list    = ref([])
const page    = ref(1)
const pages   = ref(1)
const loading = ref(false)
const error   = ref('')
const user    = ref(null)

async function load(p = 1) {
  loading.value = true
  error.value = ''
  try {
    const d = await getArticles(p)
    list.value = d.list
    page.value = d.page
    pages.value = Math.ceil(d.total / d.size) || 1
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

function go(p) {
  if (p < 1 || p > pages.value) return
  load(p)
}

onShow(() => {
  user.value = uni.getStorageSync('user') || null
  load(page.value)
})

onPullDownRefresh(async () => {
  await load(1)
  uni.stopPullDownRefresh()
})

function goDetail(id) {
  uni.navigateTo({ url: '/pages/detail/detail?id=' + id })
}

function goLogin() {
  uni.navigateTo({ url: '/pages/login/login' })
}

function goMe() {
  uni.reLaunch({ url: '/pages/me/me' })
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  padding: 24rpx 24rpx calc(140rpx + env(safe-area-inset-bottom));
}
.header {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 20rpx;
}
.h1 { font-size: 36rpx; font-weight: 700; color: #1f2329; }
.who { font-size: 24rpx; color: #2b7fff; }

.error { color: #e5484d; font-size: 26rpx; margin-bottom: 16rpx; }
.empty { color: #8a8f99; font-size: 26rpx; text-align: center; padding: 60rpx 0; }

.card {
  background: #fff;
  border-radius: 20rpx;
  padding: 26rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}
.card:active { background: #fafbfc; }
.title { display: block; font-size: 32rpx; font-weight: 600; color: #1f2329; margin-bottom: 12rpx; }
.content {
  display: block;
  font-size: 26rpx;
  color: #5c6370;
  line-height: 1.6;
  max-height: 120rpx;
  overflow: hidden;
  margin-bottom: 16rpx;
}
.meta { display: flex; gap: 24rpx; }
.meta-item { font-size: 22rpx; color: #8a8f99; }

.pager {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 32rpx;
  padding: 20rpx 0 10rpx;
}
.btn { font-size: 26rpx; color: #2b7fff; padding: 10rpx 20rpx; }
.btn.disabled { color: #c9ced6; }
.pageinfo { font-size: 24rpx; color: #8a8f99; }
</style>