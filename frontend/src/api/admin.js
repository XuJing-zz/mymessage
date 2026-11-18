import request from './request'

export const adminArticles = (params) => request.get('/admin/articles', { params })
export const adminArticle = (id) => request.get('/admin/article', { params: { id } })
export const auditArticle = (id, status) =>request.post('/admin/article/audit', { id, status })
export const adminDeleteComment = (id) => request.post('/admin/comment/delete', { id })
export const adminUsers = (params) => request.get('/admin/users', { params })
export const adminComments = (params) => request.get('/admin/comments', { params })