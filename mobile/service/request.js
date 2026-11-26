// 统一请求封装：不依赖 Cookie，登录后把 token 放到 X-Token 请求头
//
// 自动区分环境：
//   本地（localhost / 127.0.0.1）→ 直连本机 tp8
//   线上（域名 / 公网 IP）      → 走同站点 /api（Nginx 转发到 ThinkPHP）
//
// 如果手机端和接口不在同一个域名，把 PROD_BASE 改成完整地址，例如：
//   const PROD_BASE = 'https://api.example.com/api'
const DEV_BASE  = 'http://127.0.0.1/mymessage/tp8/public/index.php/api'
const PROD_BASE = (typeof location !== 'undefined')
  ? location.origin + '/api'
  : DEV_BASE

const IS_LOCAL = (typeof location !== 'undefined') &&
  (location.hostname === 'localhost' || location.hostname === '127.0.0.1' || location.hostname === '::1')

const BASE = IS_LOCAL ? DEV_BASE : PROD_BASE

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
      withCredentials: true,
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