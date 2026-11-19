<template>
  <view class="page">
    <view class="card">
      <text class="label">标题</text>
      <input class="input" v-model="title" maxlength="200" placeholder="给文章起个标题" />

      <text class="label">正文</text>
      <textarea class="textarea" v-model="content" maxlength="5000" placeholder="写点什么…" />

      <text v-if="error" class="error">{{ error }}</text>

      <button class="submit" :loading="loading" @click="submit">发布</button>
      <text class="tip">发布后需要管理员审核，审核通过才会出现在首页</text>
    </view>

    <TabBar :current="1" />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { addArticle } from '@/api/index'
import TabBar from '@/components/TabBar.vue'

const title   = ref('')
const content = ref('')
const error   = ref('')
const loading = ref(false)

onShow(() => {
  if (!uni.getStorageSync('user')) {
    uni.showToast({ title: '请先登录', icon: 'none' })
    setTimeout(() => uni.navigateTo({ url: '/pages/login/login' }), 600)
  }
})

async function submit() {
  error.value = ''
  if (!title.value.trim())   { error.value = '标题不能为空'; return }
  if (!content.value.trim()) { error.value = '正文不能为空'; return }

  loading.value = true
  try {
    await addArticle(title.value.trim(), content.value.trim())
    uni.showToast({ title: '提交成功', icon: 'success' })
    title.value = ''
    content.value = ''
    setTimeout(() => uni.reLaunch({ url: '/pages/index/index' }), 800)
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page { min-height: 100vh; padding: 24rpx 24rpx calc(140rpx + env(safe-area-inset-bottom)); }
.card { background: #fff; border-radius: 20rpx; padding: 28rpx; }
.label { display: block; font-size: 24rpx; color: #8a8f99; margin: 10rpx 0 12rpx; }
.input {
  height: 84rpx;
  padding: 0 24rpx;
  background: #f4f5f7;
  border-radius: 14rpx;
  font-size: 28rpx;
  margin-bottom: 24rpx;
}
.textarea {
  width: 100%;
  height: 360rpx;
  padding: 20rpx 24rpx;
  background: #f4f5f7;
  border-radius: 14rpx;
  font-size: 28rpx;
}
.error { display: block; color: #e5484d; font-size: 24rpx; margin: 16rpx 0; }
.submit { margin-top: 28rpx; background: #2b7fff; color: #fff; font-size: 30rpx; border-radius: 999rpx; }
.tip { display: block; margin-top: 20rpx; font-size: 22rpx; color: #b0b5bd; text-align: center; }
</style>