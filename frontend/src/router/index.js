import { createRouter, createWebHistory } from 'vue-router'
import { fetchMe } from '@/stores/auth'

const routes = [
  { path: '/login', name: 'login', component: () => import('@/views/Login.vue') },
  { path: '/register', name: 'register', component: () => import('@/views/Register.vue') },
  {
    path: '/',
    component: () => import('@/layouts/UserLayout.vue'),
    children: [
      { path: '', name: 'home', component: () => import('@/views/Home.vue') },
      { path: 'article/:id', name: 'article', component: () => import('@/views/ArticleDetail.vue') },
      {
        path: 'me',
        name: 'me',
        component: () => import('@/views/MyHome.vue'),
        meta: { requiresAuth: true },
      },
    ],
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '', redirect: '/admin/articles' },
      { path: 'articles', name: 'admin-articles', component: () => import('@/views/admin/ArticleList.vue') },
      { path: 'articles/:id', name: 'admin-article', component: () => import('@/views/admin/ArticleDetail.vue') },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) return true

  const u = await fetchMe()
  if (!u) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }
  if (to.meta.requiresAdmin && u.role !== 1) {
    return { path: '/' }
  }
  return true
})

export default router