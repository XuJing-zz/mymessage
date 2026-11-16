import request from './request'

export const myArticles = (params) => request.get('/me/articles', { params })
export const myComments = (params) => request.get('/me/comments', { params })
export const myLikes = (params) => request.get('/me/likes', { params })