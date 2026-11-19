<template>
  <view class="page">
    <view v-if="error" class="error">{{ error }}</view>

    <view v-if="article" class="card">
      <text class="title">{{ article.title }}</text>
      <text class="meta">{{ article.username }} · {{ article.created_at }}</text>
      <text class="content">{{ article.content }}</text>

      <view class="actions">
        <view class="like" :class="{ liked: article.liked }" @click="toggleLike">
          <text class="heart">{{ article.liked ? '❤️' : '🤍' }}</text>
          <text class="count">{{ article.like_count }}</text>
        </view>
        <text class="comments-count">💬 {{ comments.length }}</text>
      </view>
    </view>

    <view class="card">
      <text class="subtitle">评论（{{ comments.length }}）</text>

      <view v-if="comments.length === 0" class="empty">还没有评论</view>

      <view class="comment" v-for="c in comments" :key="c.id">
        <view class="c-head">
          <text class="c-name">{{ c.username }}</text>
          <text class="c-time">{{ c.created_at }}</text>
        </view>
        <text class="c-text">{{ c.content }}</text>
      </view>

      <view class="editor">
        <input
          class="input"
          v-model="commentText"
          placeholder="说点什么…"
          confirm-type="send"
          @confirm="sendComment"
        />
        <button class="send" size="mini" @click="sendComment">发送</button>
      </view>
    </view>

    <TabBar :current="0" />
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getArticle, like, unlike, addComment } from '@/api/index'
import TabBar from '@/components/TabBar.vue'

const id          = ref(0)
const article     = ref(null)
const comments    = ref([])
const commentText = ref('')
const error       = ref('')

async function load() {
  error.value = ''
  try {
    const d = await getArticle(id.value)
    article.value  = d.article
    comments.value = d.comments
  } catch (e) {
    error.value = e.message
  }
}

async function toggleLike() {
  if (!uni.getStorageSync('user')) {
    uni.showToast({ title: '请先登录', icon: 'none' })
    setTimeout(() => uni.navigateTo({ url: '/pages/login/login' }), 600)
    return
  }
  try {
    const d = article.value.liked
      ? await unlike(id.value)
      : await like(id.value)
    article.value.liked      = d.liked
    article.value.like_count = d.like_count
    uni.showToast({ title: d.liked ? '已点赞' : '已取消', icon: 'none' })
  } catch (e) {
    uni.showToast({ title: e.message, icon: 'none' })
  }
}

async function sendComment() {
  const text = commentText.value.trim()
  if (!text) return
  if (!uni.getStorageSync('user')) {
    uni.showToast({ title: '请先登录', icon: 'none' })
    return
  }
  try {
    await addComment(id.value, text)
    commentText.value = ''
    await load()
    uni.showToast({ title: '评论成功', icon: 'none' })
  } catch (e) {
    uni.showToast({ title: e.message, icon: 'none' })
  }
}

onLoad((options) => {
  id.value = Number(options.id || 0)
  load()
})
</script>

<style scoped>
.page { min-height: 100vh; padding: 24rpx 24rpx calc(140rpx + env(safe-area-inset-bottom)); }
.card { background: #fff; border-radius: 20rpx; padding: 26rpx; margin-bottom: 20rpx; }
.title { display: block; font-size: 36rpx; font-weight: 700; color: #1f2329; margin-bottom: 12rpx; }
.meta { display: block; font-size: 22rpx; color: #8a8f99; margin-bottom: 20rpx; }
.content { display: block; font-size: 28rpx; color: #333; line-height: 1.8; white-space: pre-wrap; }

.actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 28rpx;
  padding-top: 20rpx;
  border-top: 1rpx solid #f0f1f3;
}
.like {
  display: flex;
  align-items: center;
  gap: 10rpx;
  padding: 10rpx 24rpx;
  border: 1rpx solid #e6e8eb;
  border-radius: 999rpx;
}
.like.liked { border-color: #ffd0d0; background: #fff5f5; }
.heart { font-size: 30rpx; }
.count { font-size: 26rpx; color: #5c6370; }
.comments-count { font-size: 24rpx; color: #8a8f99; }

.subtitle { display: block; font-size: 30rpx; font-weight: 600; margin-bottom: 16rpx; }
.empty { font-size: 26rpx; color: #8a8f99; padding: 20rpx 0; }
.comment { padding: 18rpx 0; border-bottom: 1rpx solid #f4f5f7; }
.c-head { display: flex; justify-content: space-between; margin-bottom: 8rpx; }
.c-name { font-size: 24rpx; color: #2b7fff; }
.c-time { font-size: 22rpx; color: #b0b5bd; }
.c-text { font-size: 26rpx; color: #333; line-height: 1.6; }

.editor { display: flex; align-items: center; gap: 16rpx; margin-top: 24rpx; }
.input {
  flex: 1;
  height: 72rpx;
  padding: 0 24rpx;
  background: #f4f5f7;
  border-radius: 999rpx;
  font-size: 26rpx;
}
.send { margin: 0; background: #2b7fff; color: #fff; font-size: 24rpx; border-radius: 999rpx; }
.error { color: #e5484d; font-size: 26rpx; margin-bottom: 16rpx; }
</style>