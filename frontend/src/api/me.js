import request from './request'

export const myArticles = (params) => request.get('/me_articles.php', { params })
export const myComments = (params) => request.get('/me_comments.php', { params })
export const myLikes = (params) => request.get('/me_likes.php', { params })