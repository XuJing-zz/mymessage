import request from './request'

export const getComments = (articleId) =>
  request.get('/comments.php', { params: { article_id: articleId } })

export const addComment = (articleId, content) =>
  request.post('/comments_add.php', { article_id: articleId, content })

export const deleteComment = (id) => request.post('/comments_delete.php', { id })