import request from './request'

export const login = (username, password) =>
  request.post('/login', { username, password })

export const register = (username, password) =>
  request.post('/register', { username, password })

export const logout = () => request.post('/logout')

export const me = () => request.get('/me')