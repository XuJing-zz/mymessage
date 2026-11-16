import request from './request'

export const getComments = (articleId) =>
  request.get('/comments', { params: { article_id: articleId } })

export const addComment = (articleId, content) =>
  request.post('/comment', { article_id: articleId, content })

export const deleteComment = (id) => request.post('/comment/delete', { id })