<template>
  <view class="page">
    <view class="nav-links">
      <text class="nav-back" @click="goBack">‹ 首页</text>
      <navigator class="nav-link" url="/pages/admin/list" hover-class="none">文章审核</navigator>
      <navigator class="nav-link" url="/pages/admin/comments" hover-class="none">留言管理</navigator>
      <text class="nav-link active">用户</text>
    </view>

    <view v-if="error" class="error">{{ error }}</view>

    <view class="card user" v-for="u in list" :key="u.id">
      <view class="left">
        <text class="name">{{ u.username }}</text>
        <text class="muted">注册于 {{ u.created_at }}</text>
      </view>
      <text class="tag" :class="{ admin: u.role === 1 }">{{ u.role_text }}</text>
    </view>

    <view class="pager">
      <text class="btn-page" :class="{ disabled: page <= 1 }" @click="go(page - 1)">上一页</text>
      <text class="pageinfo">{{ page }} / {{ pages }} · 共 {{ total }} 人</text>
      <text class="btn-page" :class="{ disabled: page >= pages }" @click="go(page + 1)">下一页</text>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { adminUsers } from '../../service/index'

function goBack() {
  const ps = getCurrentPages()
  if (ps.length > 1) uni.navigateBack()
  else uni.reLaunch({ url: '/pages/article/list' })
}

const list    = ref([])
const page    = ref(1)
const pages   = ref(1)
const total   = ref(0)
const error   = ref('')

async function load(p = 1) {
  error.value = ''
  try {
    const d = await adminUsers(p)
    list.value = d.list
    page.value = d.page
    total.value = d.total
    pages.value = Math.ceil(d.total / d.size) || 1
  } catch (e) {
    error.value = e.message
    list.value = []
  }
}

function go(p) {
  if (p < 1 || p > pages.value) return
  load(p)
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

.card {
  width: 100%;
  box-sizing: border-box;
 background: #fff; border-radius: 20rpx; padding: 26rpx; margin-bottom: 18rpx; }

.user { display: flex; align-items: center; justify-content: space-between; }
.left { display: flex; flex-direction: column; gap: 6rpx; }
.name { font-size: 28rpx; color: #1f2329; font-weight: 600; }
.muted { font-size: 22rpx; color: #8a8f99; }
.tag { font-size: 22rpx; color: #5c6370; background: #f4f5f7; padding: 6rpx 16rpx; border-radius: 999rpx; }
.tag.admin { color: #2b7fff; background: #eef4ff; }

.pager { display: flex; align-items: center; justify-content: center; gap: 24rpx; padding: 20rpx 0; }
.btn-page { font-size: 26rpx; color: #2b7fff; padding: 10rpx 20rpx; }
.btn-page.disabled { color: #c9ced6; }
.pageinfo { font-size: 22rpx; color: #8a8f99; }
.error { color: #e5484d; font-size: 24rpx; margin-bottom: 16rpx; }
.nav-back { font-size: 26rpx; color: #666; padding: 10rpx 16rpx 10rpx 0; }
</style>