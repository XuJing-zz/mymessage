import request from './request'

export const adminArticles = (params) => request.get('/admin/articles.php', { params })
export const adminArticle = (id) => request.get('/admin/article.php', { params: { id } })
export const auditArticle = (id, status) =>
  request.post('/admin/articles_audit.php', { id, status })
export const adminDeleteComment = (id) =>
  request.post('/admin/comment_delete.php', { id })
export const adminUsers = (params) => request.get('/admin/users.php', { params })
export const adminComments = (params) => request.get('/admin/comments.php', { params })