<template>
  <view class="page">
    <view v-if="error" class="error">{{ error }}</view>

    <view v-if="article" class="card art">
      <scroll-view scroll-y class="art-body">
        <text class="title">{{ article.title }}</text>
        <text class="meta">{{ article.username }} · {{ article.created_at }}</text>
        <text class="content">{{ article.content }}</text>
      </scroll-view>

      <view class="actions">
        <view class="like" :class="{ liked: article.liked }" @click="toggleLike">
          <text class="heart">{{ article.liked ? '❤️' : '🤍' }}</text>
          <text class="count">{{ article.like_count }}</text>
        </view>
        <text class="comments-count">💬 {{ comments.length }}</text>
      </view>
    </view>

    <view class="card cmt">
      <text class="subtitle">评论（{{ comments.length }}）</text>

      <view v-if="comments.length === 0" class="empty">还没有评论</view>

      <scroll-view v-else :scroll-y="pinned" :scroll-top="cmtTop" @scroll="onCmtScroll" class="c-scroll">
        <view class="comment" v-for="c in comments" :key="c.id">
          <view class="c-head">
            <text class="c-name">{{ c.username }}</text>
            <text class="c-time">{{ c.created_at }}</text>
          </view>
          <text class="c-text">{{ c.content }}</text>
        </view>
      </scroll-view>
    </view>

    <view class="editor-bar">
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { onLoad, onReady, onPageScroll } from '@dcloudio/uni-app'
import { getArticle, like, unlike, addComment } from '../../service/index'
import TabBar from '../../components/TabBar.vue'

const id          = ref(0)
const article     = ref(null)
const comments    = ref([])
const commentText = ref('')
const error       = ref('')
const pinned      = ref(false)   // 评论卡片是否已顶到页面顶部（到底了）
const cmtTop      = ref(0)      // 评论列表内部滚动位置
let   pageHeight  = 0

// 记录整页高度，用来判断页面是否已经滚到底
function onCmtScroll(e) {
  cmtTop.value = Math.round((e.detail && e.detail.scrollTop) || 0)
}

function measurePage() {
  uni.createSelectorQuery().select('.page').boundingClientRect((rect) => {
    if (rect) pageHeight = rect.height
  }).exec()
}

// #ifdef H5
function onWinScroll() {
  const max = document.documentElement.scrollHeight - window.innerHeight
  const next = window.scrollY >= max - 2
  if (!next && pinned.value) cmtTop.value = 0   // 页面回滚时把列表复位
  pinned.value = next
}
onMounted(() => {
  setTimeout(measurePage, 500)
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => window.removeEventListener('scroll', onWinScroll))
// #endif

// #ifndef H5
onReady(() => setTimeout(measurePage, 500))
onPageScroll((e) => {
  const win = uni.getSystemInfoSync().windowHeight
  const max = Math.max(0, pageHeight - win)
  const next = e.scrollTop >= max - 2
  if (!next && pinned.value) cmtTop.value = 0
  pinned.value = next
})
// #endif


async function load() {
  error.value = ''
  try {
    const d = await getArticle(id.value)
    article.value  = d.article
    comments.value = d.comments
    setTimeout(measurePage, 100)
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
/* 整页布局：纯 block + 固定高度 + calc 百分比 */
.page {
  position: relative;
  width: 100%;
  min-height: calc(100vh - var(--window-top, 0px));
  box-sizing: border-box;
  padding: 24rpx 24rpx calc(238rpx + env(safe-area-inset-bottom));
  background: #f5f6f8;
}

.card {
  display: block;
  width: 100%;
  box-sizing: border-box;
  background: #fff;
  border-radius: 20rpx;
  padding: 26rpx;
  border: 1rpx solid #eef0f3;
  overflow: hidden;
}

/* 文章区：固定一屏高，正文滚动，点赞行钉在卡片底部 */
.art {
  position: relative;
  height: calc(100vh - var(--window-top, 0px) - 262rpx - env(safe-area-inset-bottom));
  margin-bottom: 20rpx;
  overflow: hidden;
}
.art-body {
  display: block;
  width: 100%;
  height: calc(100% - 104rpx);
  box-sizing: border-box;
}

.title { display: block; font-size: 36rpx; font-weight: 700; color: #1f2329; line-height: 1.45; margin-bottom: 12rpx; }
.meta { display: block; font-size: 22rpx; color: #8a8f99; margin-bottom: 20rpx; }
.content { display: block; font-size: 28rpx; color: #333; line-height: 1.85; white-space: pre-wrap; word-break: break-all; }

/* 点赞行：inline-block + float */
.actions {
  position: absolute;
  left: 26rpx;
  right: 26rpx;
  bottom: 26rpx;
  height: 84rpx;
  padding-top: 20rpx;
  box-sizing: border-box;
  border-top: 1rpx solid #f0f1f3;
  overflow: hidden;
}
.like {
  display: inline-block;
  padding: 10rpx 24rpx;
  border: 1rpx solid #e6e8eb;
  border-radius: 999rpx;
}
.like.liked { border-color: #ffd0d0; background: #fff5f5; }
.heart { font-size: 30rpx; vertical-align: middle; }
.count { font-size: 26rpx; color: #5c6370; margin-left: 10rpx; vertical-align: middle; }
.comments-count { float: right; font-size: 24rpx; color: #8a8f99; line-height: 60rpx; }

/* 评论区：跟着评论数量自然变高 */
.cmt {
  height: calc(100vh - var(--window-top, 0px) - 262rpx - env(safe-area-inset-bottom));
  min-height: 0;
  margin-bottom: 0;
  overflow: hidden;
}

.subtitle {
  display: block;
  height: 48rpx;
  line-height: 48rpx;
  font-size: 30rpx;
  font-weight: 600;
  color: #1f2329;
  margin-bottom: 6rpx;
}

.empty {
  display: block;
  padding: 24rpx 0;
  font-size: 26rpx;
  color: #8a8f99;
}

/* 评论列表：卡片内固定高度，到底后才允许内部滚动 */
.c-scroll {
  width: 100%;
  height: calc(100% - 54rpx);
  box-sizing: border-box;
  border-top: 1rpx solid #f2f3f5;
}

.comment {
  padding: 22rpx 14rpx 22rpx 0;
  border-bottom: 1rpx solid #f6f7f9;
}
.comment:last-child { border-bottom: none; }

/* 评论头：左名字右时间，用 float */
.c-head { overflow: hidden; margin-bottom: 10rpx; }
.c-name { float: left; font-size: 26rpx; font-weight: 600; color: #2b7fff; line-height: 36rpx; }
.c-time { float: right; font-size: 22rpx; color: #a6acb8; line-height: 36rpx; }
.c-text {
  display: block;
  clear: both;
  font-size: 27rpx;
  color: #333;
  line-height: 1.7;
  word-break: break-all;
}

/* 底部输入栏：紧贴 TabBar 上沿，纯白不透明 */
.editor-bar {
  position: fixed;
  left: 0;
  right: 0;
  bottom: calc(110rpx + env(safe-area-inset-bottom));
  margin: 0;
  z-index: 99;
  box-sizing: border-box;
  padding: 16rpx 24rpx;
  background: #ffffff;
  border-top: 1rpx solid #ebedf0;
  box-shadow: 0 -4rpx 12rpx rgba(0, 0, 0, 0.05);
}
.editor { position: relative; height: 72rpx; }
.input {
  display: inline-block;
  width: calc(100% - 136rpx);
  height: 72rpx;
  padding: 0 24rpx;
  box-sizing: border-box;
  background: #f4f5f7;
  border-radius: 999rpx;
  font-size: 26rpx;
  vertical-align: middle;
}
.send {
  display: inline-block;
  width: 120rpx;
  height: 72rpx;
  line-height: 72rpx;
  margin: 0 0 0 16rpx;
  padding: 0;
  background: #2b7fff;
  color: #fff;
  font-size: 26rpx;
  border-radius: 999rpx;
  vertical-align: middle;
}
.error { color: #e5484d; font-size: 26rpx; margin-bottom: 12rpx; }
</style>