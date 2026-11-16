import request from './request'

export const login = (username, password) =>
  request.post('/login.php', { username, password })

export const register = (username, password) =>
  request.post('/register.php', { username, password })

export const logout = () => request.post('/logout.php')

export const me = () => request.get('/me.php')