import request from './request'

export const like = (articleId) => request.post('/like', { article_id: articleId })
export const unlike = (articleId) => request.post('/unlike', { article_id: articleId })