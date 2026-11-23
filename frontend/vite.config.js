import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// 生产构建放在 /admin/ 子路径，开发时用 /
export default defineConfig(({ mode }) => ({
  base: mode === 'production' ? '/admin/' : '/',
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5174,
    proxy: {
      // /api/xxx -> http://localhost/mymessage/tp8/public/index.php/api/xxx
      '/api': {
        target: 'http://localhost',
        changeOrigin: true,
        rewrite: (p) => '/mymessage/tp8/public/index.php' + p,
      },
    },
  },
}))