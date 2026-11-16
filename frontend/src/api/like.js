import request from './request'

export const like = (articleId) => request.post('/likes.php', { article_id: articleId })
export const unlike = (articleId) => request.post('/unlikes.php', { article_id: articleId })