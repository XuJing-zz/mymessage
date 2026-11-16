import request from './request'

export const getArticles = (params) => request.get('/articles.php', { params })
export const getArticleDetail = (id) => request.get('/article_comment.php', { params: { id } })
export const addArticle = (data) => request.post('/article_add.php', data)
export const deleteArticle = (id) => request.post('/articles_delete.php', { id })