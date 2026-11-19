// 统一请求封装：H5 通过 vite 代理转发到 ThinkPHP
const BASE = '/api'

function request(options) {
  return new Promise((resolve, reject) => {
    uni.request({
      url: BASE + options.url,
      method: options.method || 'GET',
      data: options.data || {},
      header: { 'content-type': 'application/x-www-form-urlencoded' },
      withCredentials: true,          // H5 带 Cookie（Session）
      success: (res) => {
        const d = res.data || {}
        if (d.code === 0) {
          resolve(d.data)
          return
        }
        if (d.code === 401) {
          uni.removeStorageSync('user')
        }
        reject(new Error(d.msg || '请求失败'))
      },
      fail: (err) => {
        reject(new Error(err.errMsg || '网络错误'))
      },
    })
  })
}

export default request