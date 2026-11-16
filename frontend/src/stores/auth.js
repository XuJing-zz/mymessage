import { ref } from 'vue'
import * as authApi from '@/api/auth'

const user = ref(null)
let loaded = false

export function useAuth() {
  return { user }
}

export async function fetchMe(force = false) {
  if (loaded && !force) return user.value
  try {
    user.value = await authApi.me()
  } catch {
    user.value = null
  }
  loaded = true
  return user.value
}

export async function login(username, password) {
  const u = await authApi.login(username, password)
  user.value = u
  loaded = true
  return u
}

export async function register(username, password) {
  const u = await authApi.register(username, password)
  user.value = u
  loaded = true
  return u
}

export async function logout() {
  try {
    await authApi.logout()
  } catch {
    // 忽略
  }
  user.value = null
  loaded = true
}

export function isAdmin() {
  return user.value?.role === 1
}