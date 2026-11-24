<template>
  <view class="page">
    <view class="header">
      <text class="h1">推荐</text>
    </view>

    <view v-if="error" class="error">{{ error }}</view>

    <view v-if="!loading && list.length === 0 && !error" class="empty">还没有文章</view>

    <navigator
      class="card"
      v-for="a in list"
      :key="a.id"
      :url="'/pages/detail/detail?id=' + a.id"
      hover-class="none"
    >
      <text class="title">{{ a.title }}</text>
      <text class="content">{{ a.content }}</text>
      <view class="meta">
        <text class="meta-item name">{{ a.username }}</text>
        <text class="meta-item">♡ {{ a.like_count }}</text>
        <text class="meta-item">💬 {{ a.comment_count }}</text>
      </view>
    </navigator>

    <!-- 上拉加载 -->
    <view class="more" v-if="list.length > 0">
      <text v-if="more || loading" class="more-txt">加载中…</text>
      <text v-else class="more-txt" @click="loadMore">上拉加载更多</text>
    </view>

    <TabBar :current="0" />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow, onPullDownRefresh, onReachBottom } from '@dcloudio/uni-app'
import { getArticles, getCounts } from '../../service/index'
import TabBar from '../../components/TabBar.vue'

const MAX           = 20   // 首页最多显示 20 条
const INIT_SIZE     = 20   // 首次进页面随机取 20 条
const REFRESH_SIZE  = 5    // 下拉刷新往最上面插 5 条
const MORE_SIZE     = 10   // 上拉加载再随机取 10 条

const list    = ref([])
const loading = ref(false)
const more    = ref(false)
const error   = ref('')

// 随机捞 n 条“当前列表里没有的”文章
async function pickRandom(n) {
  const have   = new Set(list.value.map((x) => x.id))
  const picked = []
  for (let attempt = 0; attempt < 4 && picked.length < n; attempt++) {
    const d = await getArticles(1, Math.min(50, Math.max(n * 3, 12)), 1)
    for (const a of d.list) {
      if (picked.length >= n) break
      if (have.has(a.id)) continue
      if (picked.some((x) => x.id === a.id)) continue
      picked.push(a)
    }
  }
  return picked
}

async function initFeed() {
  loading.value = true
  error.value = ''
  try {
    list.value = await pickRandom(INIT_SIZE)
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

// 下拉刷新：新内容插到最上面，超出 20 条从**末尾**丢掉最老的
async function refreshFeed() {
  try {
    const fresh = await pickRandom(REFRESH_SIZE)
    if (!fresh.length && !list.value.length) { await initFeed(); return 0 }
    list.value = fresh.concat(list.value)
    if (list.value.length > MAX) list.value = list.value.slice(0, MAX)
    return fresh.length
  } catch (e) {
    error.value = e.message
    return 0
  }
}

// 上拉加载：新内容加在末尾，超出 20 条从**开头**丢掉最早的
async function loadMore() {
  if (more.value || loading.value) return
  more.value = true
  error.value = ''
  try {
    const fresh = await pickRandom(MORE_SIZE)
    if (!fresh.length && !list.value.length) return await initFeed()
    list.value = list.value.concat(fresh)
    if (list.value.length > MAX) {
      list.value = list.value.slice(list.value.length - MAX)
    }
  } catch (e) {
    error.value = e.message
  } finally {
    more.value = false
  }
}

onShow(async () => {
  if (!list.value.length) return initFeed()

  // 从详情页返回时，把当前这 20 条的点赞/评论数同步一下（不然数字是旧的）
  try {
    const m = await getCounts(list.value.map((x) => x.id))
    list.value = list.value.map((x) => ({
      ...x,
      like_count:    m[x.id] ? m[x.id].like_count    : x.like_count,
      comment_count: m[x.id] ? m[x.id].comment_count : x.comment_count,
    }))
  } catch (e) { /* 同步失败不影响浏览 */ }
})

onPullDownRefresh(async () => {
  try {
    const n = await refreshFeed()
    if (n > 0) uni.showToast({ title: '已更新 ' + n + ' 条', icon: 'none', duration: 1200 })
  } catch (e) {
    uni.showToast({ title: '刷新失败', icon: 'none' })
  } finally {
    uni.stopPullDownRefresh()   // 成功失败都要回弹
  }
})


onReachBottom(() => {
  loadMore()
})
</script>

<style scoped>
.page {
  min-height: 100vh;
  padding: 0 24rpx calc(140rpx + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #f5f6f8;
}

/* 顶部标题栏 */
.header {
  padding: 24rpx 8rpx 20rpx;
}
.h1 {
  font-size: 44rpx;
  font-weight: 700;
  color: #1f2329;
  letter-spacing: 1rpx;
}

/* 提示 */
.error {
  color: #e5484d;
  font-size: 24rpx;
  padding: 12rpx 8rpx 20rpx;
}
.empty {
  text-align: center;
  color: #8a8f99;
  font-size: 26rpx;
  padding: 160rpx 0;
}

/* 文章卡片 */
.card {
  width: 100%;
  box-sizing: border-box;
  display: block;
  background: #fff;
  border-radius: 20rpx;
  padding: 28rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(17, 24, 39, 0.04);
}
.title {
  display: block;
  font-size: 32rpx;
  font-weight: 600;
  color: #1f2329;
  line-height: 1.45;
  margin-bottom: 14rpx;
}
.content {
  font-size: 26rpx;
  color: #5c6370;
  line-height: 1.6;
  margin-bottom: 20rpx;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.meta {
  display: flex;
  align-items: center;
  gap: 24rpx;
  padding-top: 18rpx;
  border-top: 1rpx solid #f2f3f5;
}
.meta-item {
  font-size: 22rpx;
  color: #8a8f99;
}
.meta-item.name {
  color: #5c6370;
}

/* 上拉加载 */
.more {
  padding: 24rpx 0 8rpx;
  text-align: center;
}
.more-txt {
  font-size: 24rpx;
  color: #a6acb8;
}
</style>
