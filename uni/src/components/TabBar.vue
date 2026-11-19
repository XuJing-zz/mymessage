<template>
  <view class="tabbar">
    <view
      v-for="(item, i) in items"
      :key="i"
      class="tab-item"
      :class="{ active: current === i }"
      @click="go(i)"
    >
      <text class="tab-icon">{{ item.icon }}</text>
      <text class="tab-text">{{ item.text }}</text>
    </view>
  </view>
</template>

<script setup>
const props = defineProps({
  current: { type: Number, default: 0 },
})

const items = [
  { text: '首页', icon: '🏠', url: '/pages/index/index' },
  { text: '发文章', icon: '✍️', url: '/pages/publish/publish' },
  { text: '我的', icon: '👤', url: '/pages/me/me' },
]

function go(i) {
  if (i === props.current) return
  uni.reLaunch({ url: items[i].url })
}
</script>

<style scoped>
.tabbar {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 100;
  display: flex;
  height: 110rpx;
  padding-bottom: env(safe-area-inset-bottom);
  background: #ffffff;
  border-top: 1rpx solid #ebedf0;
  box-shadow: 0 -4rpx 16rpx rgba(0, 0, 0, 0.04);
}
.tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6rpx;
}
.tab-item:active { transform: scale(0.95); }
.tab-icon { font-size: 36rpx; line-height: 1; }
.tab-text { font-size: 22rpx; color: #8a8f99; line-height: 1; }
.tab-item.active .tab-text { color: #2b7fff; font-weight: 600; }

@media screen and (min-width: 768px) {
  .tabbar { max-width: 480px; margin: 0 auto; border-radius: 20rpx 20rpx 0 0; }
}
</style>