// 统一请求封装：直接连 ThinkPHP（后端已开 CORS）
// 手机端不依赖 Cookie：登录后拿 token，放到 X-Token 请求头里
// 模拟器：用 127.0.0.1（localhost 可能被解析成 IPv6 ::1，而 nginx 只监听 IPv4，会连接超时）
// 真机调试：把 HOST 改成电脑的局域网 IP，例如 '192.168.3.127'，手机和电脑连同一个 Wi-Fi
const HOST = '127.0.0.1'

// 本地开发：直接连本机 TP 的完整路径
let BASE = 'http://' + HOST + '/mymessage/tp8/public/index.php/api'

// H5 生产构建：请求同域的 /api，由 Nginx 伪静态转给 ThinkPHP
// #ifdef H5
if (typeof location !== 'undefined' && process.env.NODE_ENV === 'production') {
  BASE = location.origin + '/api'
}
// #endif

function request(options) {
  return new Promise((resolve, reject) => {
    const token = uni.getStorageSync('token') || ''
    const header = { 'content-type': 'application/x-www-form-urlencoded' }
    if (token) header['X-Token'] = token

    uni.request({
      url: BASE + options.url,
      method: options.method || 'GET',
      data: options.data || {},
      header: header,
      withCredentials: true,          // 浏览器环境下顺便带 Cookie
      success: (res) => {
        const d = res.data

        if (typeof d !== 'object' || d === null) {
          reject(new Error('接口返回异常（HTTP ' + res.statusCode + '）：' + BASE))
          return
        }

        if (d.code === 0) {
          resolve(d.data)
          return
        }

        if (d.code === 401) {
          uni.removeStorageSync('user')
          uni.removeStorageSync('token')
        }
        reject(new Error(d.msg || '请求失败'))
      },
      fail: (err) => {
        reject(new Error(err.errMsg || '网络错误（检查后端是否启动）'))
      },
    })
  })
}

export default request