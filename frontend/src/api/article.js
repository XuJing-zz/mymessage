import request from './request'

export const getArticles = (params) => request.get('/articles', { params })
export const getArticleDetail = (id) => request.get('/article', { params: { id } })
export const addArticle = (data) => request.post('/article', data)
export const deleteArticle = (id) => request.post('/article/delete', { id })