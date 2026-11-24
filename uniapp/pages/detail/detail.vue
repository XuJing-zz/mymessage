<template>
  <view class="page">
    <view v-if="error" class="error">{{ error }}</view>

    <view v-if="article" class="card art">
      <text class="title">{{ article.title }}</text>
      <text class="meta">{{ article.username }} · {{ article.created_at }}</text>
      <text class="content">{{ article.content }}</text>
      <view class="actions">
        <view class="like" :class="{ liked: article.liked }" @click="onLike">
          <text class="heart">{{ article.liked ? '❤️' : '🤍' }}</text>
          <text class="count">{{ article.like_count }}</text>
        </view>
        <text class="cmt-count">💬 {{ comments.length }}</text>
      </view>
    </view>

    <view class="card cmt" :class="{ pinned }">
      <text class="subtitle">评论（{{ comments.length }}）</text>
      <view v-if="!comments.length" class="empty">还没有评论</view>
      <scroll-view v-else :scroll-y="true" :scroll-top="cmtTop" @scroll="onCmtScroll" class="c-scroll">
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
      <input class="input" v-model="commentText" placeholder="说点什么…" confirm-type="send" @confirm="onComment" />
      <button class="send" size="mini" @click="onComment">发送</button>
    </view>

    <TabBar :current="0" />
  </view>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { onLoad, onReady, onPageScroll } from '@dcloudio/uni-app'
import { getArticle, like, unlike, addComment } from '../../service/index'
import TabBar from '../../components/TabBar.vue'

const id = ref(0)
const article = ref(null)
const comments = ref([])
const commentText = ref('')
const error = ref('')
const pinned = ref(false)
const cmtTop = ref(0)
let cmtOffset = 0

const logged = () => !!uni.getStorageSync('user')

// 未登录：先弹提示，用户点了「去登录」再跳，并记住回来
function needLogin(tip) {
  if (logged()) return false
  uni.showModal({
    title: '未登录',
    content: tip,
    confirmText: '去登录',
    success: (r) => {
      if (r.confirm) {
        const back = '/pages/detail/detail?id=' + id.value
        uni.navigateTo({ url: '/pages/login/login?redirect=' + encodeURIComponent(back) })
      }
    },
  })
  return true
}

function onCmtScroll(e) { cmtTop.value = Math.round(e.detail?.scrollTop || 0) }

function measure() {
  uni.createSelectorQuery().select('.cmt').boundingClientRect()
    .selectViewport().scrollOffset().exec((r) => {
      if (r[0] && r[1]) cmtOffset = r[0].top + r[1].scrollTop
    })
}

function setPinned(next) {
  if (!next && pinned.value) cmtTop.value = 0
  pinned.value = next
}

// #ifdef H5
const topPad = () => parseInt(getComputedStyle(document.documentElement).getPropertyValue('--window-top')) || 0
function onWinScroll() {
  const el = document.querySelector('.cmt')
  if (el) setPinned(el.getBoundingClientRect().top <= topPad() + 2)
}
onMounted(() => {
  setTimeout(measure, 500)
  window.addEventListener('scroll', onWinScroll, { passive: true })
  setTimeout(onWinScroll, 800)
})
onUnmounted(() => window.removeEventListener('scroll', onWinScroll))
// #endif

// #ifndef H5
onReady(() => setTimeout(measure, 500))
onPageScroll((e) => setPinned(e.scrollTop >= cmtOffset - 2))
// #endif

async function load() {
  error.value = ''
  try {
    const d = await getArticle(id.value)
    article.value = d.article
    comments.value = d.comments
    setTimeout(measure, 120)
  } catch (e) { error.value = e.message }
}

async function onLike() {
  if (needLogin('登录后才能点赞，是否前往登录？')) return
  try {
    const d = article.value.liked ? await unlike(id.value) : await like(id.value)
    article.value.liked = d.liked
    article.value.like_count = d.like_count
    uni.showToast({ title: d.liked ? '已点赞' : '已取消', icon: 'none' })
  } catch (e) { uni.showToast({ title: e.message, icon: 'none' }) }
}

async function onComment() {
  const text = commentText.value.trim()
  if (!text) return
  if (needLogin('登录后才能评论，是否前往登录？')) return
  try {
    await addComment(id.value, text)
    commentText.value = ''
    await load()
    uni.showToast({ title: '评论成功', icon: 'none' })
  } catch (e) { uni.showToast({ title: e.message, icon: 'none' }) }
}

onLoad((o) => { id.value = Number(o.id || 0); load() })
</script>

<style scoped>
.page { min-height: calc(100vh - var(--window-top, 0px)); box-sizing: border-box; padding: 24rpx 24rpx calc(238rpx + env(safe-area-inset-bottom)); background: #f5f6f8; }
.card { box-sizing: border-box; width: 100%; background: #fff; border-radius: 20rpx; padding: 26rpx; border: 1rpx solid #eef0f3; }
.art { margin-bottom: 20rpx; }

.title { display: block; font-size: 36rpx; font-weight: 700; color: #1f2329; line-height: 1.45; margin-bottom: 12rpx; }
.meta { display: block; font-size: 22rpx; color: #8a8f99; margin-bottom: 20rpx; }
.content { display: block; font-size: 28rpx; color: #333; line-height: 1.85; white-space: pre-wrap; word-break: break-all; }

.actions { margin-top: 20rpx; padding-top: 20rpx; height: 84rpx; box-sizing: border-box; border-top: 1rpx solid #f0f1f3; overflow: hidden; }
.like { display: inline-block; padding: 10rpx 24rpx; border: 1rpx solid #e6e8eb; border-radius: 999rpx; }
.like.liked { border-color: #ffd0d0; background: #fff5f5; }
.heart { font-size: 30rpx; vertical-align: middle; }
.count { font-size: 26rpx; color: #5c6370; margin-left: 10rpx; vertical-align: middle; }
.cmt-count { float: right; font-size: 24rpx; color: #8a8f99; line-height: 60rpx; }

.cmt { position: sticky; top: var(--window-top, 0px); z-index: 5; height: calc(100vh - var(--window-top, 0px) - 262rpx - env(safe-area-inset-bottom)); overflow: hidden; }
.cmt.pinned { border-radius: 0; border-left: none; border-right: none; padding-left: 24rpx; padding-right: 24rpx; }
.subtitle { display: block; height: 48rpx; line-height: 48rpx; font-size: 30rpx; font-weight: 600; color: #1f2329; margin-bottom: 6rpx; }
.empty { display: block; padding: 24rpx 0; font-size: 26rpx; color: #8a8f99; }
.c-scroll { width: 100%; height: calc(100% - 54rpx); box-sizing: border-box; border-top: 1rpx solid #f2f3f5; }

.comment { padding: 22rpx 14rpx 22rpx 0; border-bottom: 1rpx solid #f6f7f9; }
.comment:last-child { border-bottom: none; }
.c-head { overflow: hidden; margin-bottom: 10rpx; }
.c-name { float: left; font-size: 26rpx; font-weight: 600; color: #2b7fff; line-height: 36rpx; }
.c-time { float: right; font-size: 22rpx; color: #a6acb8; line-height: 36rpx; }
.c-text { display: block; clear: both; font-size: 27rpx; color: #333; line-height: 1.7; word-break: break-all; }

.editor-bar { position: fixed; left: 0; right: 0; bottom: calc(110rpx + env(safe-area-inset-bottom)); z-index: 99; box-sizing: border-box; padding: 16rpx 24rpx; background: #fff; border-top: 1rpx solid #ebedf0; }
.input { display: inline-block; width: calc(100% - 136rpx); height: 72rpx; padding: 0 24rpx; box-sizing: border-box; background: #f4f5f7; border-radius: 999rpx; font-size: 26rpx; vertical-align: middle; }
.send { display: inline-block; width: 120rpx; height: 72rpx; line-height: 72rpx; margin-left: 16rpx; padding: 0; background: #2b7fff; color: #fff; font-size: 26rpx; border-radius: 999rpx; vertical-align: middle; }
.error { color: #e5484d; font-size: 26rpx; margin-bottom: 12rpx; }
</style>
