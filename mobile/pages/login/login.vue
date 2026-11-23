<template>
  <view class="page">
    <view class="card">
      <view class="tabs">
        <view class="tab" :class="{ active: mode === 'login' }" @click="switchMode('login')">登录</view>
        <view class="tab" :class="{ active: mode === 'register' }" @click="switchMode('register')">注册</view>
      </view>

      <view class="field">
        <text class="label">用户名</text>
        <input class="input" v-model="username" placeholder="3-20 个字符" />
      </view>

      <view class="field">
        <text class="label">密码</text>
        <input class="input" v-model="password" password placeholder="至少 6 位" />
      </view>

      <view class="field" v-if="mode === 'register'">
        <text class="label">确认密码</text>
        <input class="input" v-model="password2" password placeholder="再输一次" />
      </view>

      <text v-if="error" class="error">{{ error }}</text>

      <button class="submit" :loading="loading" @click="submit">
        {{ mode === 'login' ? '登录' : '注册' }}
      </button>

      <text class="tip">测试账号：admin / 123456</text>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { login, register, me } from '../../service/index'

const mode      = ref('login')
const username  = ref('')
const password  = ref('')
const password2 = ref('')
const error     = ref('')
const loading   = ref(false)

// 用函数切换，避免模板里直接给 ref 赋值
function switchMode(m) {
  error.value = ''
  mode.value = m
}

async function submit() {
  error.value = ''

  if (username.value.trim().length < 3) { error.value = '用户名至少 3 个字符'; return }
  if (password.value.length < 6)        { error.value = '密码至少 6 位'; return }
  if (mode.value === 'register' && password.value !== password2.value) {
    error.value = '两次密码不一致'
    return
  }

  loading.value = true
  try {
    let res
    if (mode.value === 'login') {
      res = await login(username.value.trim(), password.value)
    } else {
      res = await register(username.value.trim(), password.value, password2.value)
    }

    if (res && res.token) {
      uni.setStorageSync('token', res.token)
    }

    uni.setStorageSync('user', { id: res.id, username: res.username, role: res.role })

    try {
      const u2 = await me()
      uni.setStorageSync('user', u2)
    } catch (e2) {
      console.warn('me 校验失败：', e2.message)
    }

    uni.showToast({
      title: mode.value === 'login' ? '登录成功' : '注册成功',
      icon: 'success',
    })
    setTimeout(() => uni.reLaunch({ url: '/pages/article/list' }), 700)
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page { min-height: 100vh; padding: 60rpx 24rpx; }
.card { background: #fff; border-radius: 24rpx; padding: 40rpx 32rpx; }

.tabs { display: flex; gap: 16rpx; margin-bottom: 36rpx; }
.tab {
  padding: 12rpx 8rpx 16rpx;
  font-size: 32rpx;
  color: #8a8f99;
}
.tab.active { color: #1f2329; font-weight: 700; border-bottom: 4rpx solid #2b7fff; }

.field { margin-bottom: 28rpx; }
.label { display: block; font-size: 24rpx; color: #8a8f99; margin-bottom: 10rpx; }
.input {
  height: 84rpx;
  padding: 0 24rpx;
  background: #f4f5f7;
  border-radius: 14rpx;
  font-size: 28rpx;
}
.error { display: block; color: #e5484d; font-size: 26rpx; margin-bottom: 20rpx; }
.submit {
  margin-top: 12rpx;
  background: #2b7fff;
  color: #fff;
  font-size: 30rpx;
  border-radius: 999rpx;
}
.tip { display: block; margin-top: 24rpx; font-size: 22rpx; color: #b0b5bd; text-align: center; }
</style>
