<template>
  <view class="page">
    <view class="nav-links">
      <text class="nav-back" @click="goBack">‹ 首页</text>
      <navigator class="nav-link" url="/pages/admin/list" hover-class="none">文章审核</navigator>
      <text class="nav-link active">留言管理</text>
      <navigator class="nav-link" url="/pages/admin/users" hover-class="none">用户</navigator>
    </view>

    <view class="search">
      <input
        class="search-input"
        v-model="keyword"
        placeholder="搜评论内容或文章标题"
        confirm-type="search"
        @confirm="doSearch"
      />
      <text class="search-btn" @click="doSearch">搜索</text>
      <text v-if="keyword" class="clear" @click="clearKeyword">清除</text>
    </view>

    <view v-if="error" class="error">{{ error }}</view>
    <view v-if="!loading && groups.length === 0" class="empty">没有留言</view>

    <!-- 按文章分组：同一篇文章的留言归在一起 -->
    <view class="group" v-for="g in groups" :key="g.article_id">
      <view class="group-head">
        <text class="group-title">📄 {{ g.article_title }}</text>
        <text class="group-count">{{ g.items.length }} 条</text>
      </view>

      <view class="card" v-for="c in g.items" :key="c.id">
        <view class="row">
          <text class="name">{{ c.username }}</text>
          <text class="muted">{{ c.created_at }}</text>
        </view>
        <text class="content">{{ c.content }}</text>
        <view class="row">
          <text class="muted">文章 #{{ c.article_id }}</text>
          <text class="del" @click="remove(c.id)">删除</text>
        </view>
      </view>
    </view>

    <view class="pager">
      <text class="btn-page" :class="{ disabled: page <= 1 }" @click="go(page - 1)">上一页</text>
      <text class="pageinfo">{{ page }} / {{ pages }}（共 {{ total }} 条）</text>
      <text class="btn-page" :class="{ disabled: page >= pages }" @click="go(page + 1)">下一页</text>
    </view>
  </view>
</template>

<script setup>
import { ref, computed } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { adminComments, adminDeleteComment } from '../../service/index'

function goBack() {
  const ps = getCurrentPages()
  if (ps.length > 1) uni.navigateBack()
  else uni.reLaunch({ url: '/pages/article/list' })
}

const list    = ref([])
const page    = ref(1)
const pages   = ref(1)
const total   = ref(0)
const keyword = ref('')
const loading = ref(false)
const error   = ref('')

// 按 article_id 分组，保持后端返回的顺序
const groups = computed(() => {
  const map = new Map()
  for (const c of list.value) {
    if (!map.has(c.article_id)) {
      map.set(c.article_id, { article_id: c.article_id, article_title: c.article_title, items: [] })
    }
    map.get(c.article_id).items.push(c)
  }
  return Array.from(map.values())
})

async function load(p = 1) {
  loading.value = true
  error.value = ''
  try {
    const d = await adminComments(p, keyword.value.trim())
    list.value = d.list
    page.value = d.page
    total.value = d.total
    pages.value = Math.ceil(d.total / d.size) || 1
  } catch (e) {
    error.value = e.message
    list.value = []
  } finally {
    loading.value = false
  }
}

function doSearch() {
  load(1)
}

function clearKeyword() {
  keyword.value = ''
  load(1)
}

function go(p) {
  if (p < 1 || p > pages.value) return
  load(p)
}

function remove(id) {
  uni.showModal({
    title: '确认',
    content: '确定删除这条留言？',
    success: async (res) => {
      if (!res.confirm) return
      try {
        await adminDeleteComment(id)
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
.nav-links { display: flex; gap: 32rpx; padding: 10rpx 8rpx 24rpx; }
.nav-link { font-size: 28rpx; color: #8a8f99; }
.nav-link.active { color: #1f2329; font-weight: 700; border-bottom: 4rpx solid #2b7fff; padding-bottom: 8rpx; }

.search { display: flex; align-items: center; gap: 16rpx; margin-bottom: 20rpx; }
.search-input {
  flex: 1;
  height: 68rpx;
  padding: 0 24rpx;
  box-sizing: border-box;
  background: #fff;
  border-radius: 999rpx;
  font-size: 26rpx;
}
.search-btn { font-size: 26rpx; color: #2b7fff; padding: 8rpx 10rpx; }
.clear { font-size: 24rpx; color: #8a8f99; }

.group { margin-bottom: 24rpx; }
.group-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10rpx 8rpx 14rpx;
}
.group-title { font-size: 26rpx; font-weight: 600; color: #1f2329; }
.group-count { font-size: 22rpx; color: #8a8f99; }

.card {
  width: 100%;
  box-sizing: border-box;
  background: #fff; border-radius: 20rpx; padding: 24rpx; margin-bottom: 18rpx;
}
.row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10rpx; }
.name { font-size: 26rpx; color: #2b7fff; }
.muted { font-size: 22rpx; color: #8a8f99; }
.content { display: block; font-size: 26rpx; color: #333; line-height: 1.6; margin-bottom: 12rpx; }
.del { font-size: 24rpx; color: #e5484d; }

.pager { display: flex; align-items: center; justify-content: center; gap: 24rpx; padding: 20rpx 0; }
.btn-page { font-size: 26rpx; color: #2b7fff; padding: 10rpx 20rpx; }
.btn-page.disabled { color: #c9ced6; }
.pageinfo { font-size: 24rpx; color: #8a8f99; }
.empty { text-align: center; color: #8a8f99; font-size: 26rpx; padding: 60rpx 0; }
.error { color: #e5484d; font-size: 24rpx; margin-bottom: 16rpx; }
.nav-back { font-size: 26rpx; color: #666; padding: 10rpx 16rpx 10rpx 0; }
</style>
