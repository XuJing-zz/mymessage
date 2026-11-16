import axios from 'axios'

const request = axios.create({
  baseURL: '/api',
  timeout: 10000,
  withCredentials: true,
})

request.interceptors.response.use(
  (res) => {
    const d = res.data
    if (d.code !== 0) {
      return Promise.reject(new Error(d.msg || '请求失败'))
    }
    return d.data
  },
  (err) => {
    const msg = err.response?.data?.msg || err.message || '网络错误'
    return Promise.reject(new Error(msg))
  }
)

export default request