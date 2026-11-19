import request from './request'

// 认证
export const login    = (username, password) => request({ url: '/login', method: 'POST', data: { username, password } })
export const register = (username, password, password2) => request({ url: '/register', method: 'POST', data: { username, password, password2 } })
export const logout   = () => request({ url: '/logout', method: 'POST' })
export const me       = () => request({ url: '/me' })

// 文章
export const getArticles  = (page = 1) => request({ url: '/articles?page=' + page })
export const getArticle   = (id) => request({ url: '/article?id=' + id })
export const addArticle   = (title, content) => request({ url: '/article', method: 'POST', data: { title, content } })

// 评论
export const getComments  = (articleId) => request({ url: '/comments?article_id=' + articleId })
export const addComment   = (articleId, content) => request({ url: '/comment', method: 'POST', data: { article_id: articleId, content } })
export const deleteComment = (id) => request({ url: '/comment/delete', method: 'POST', data: { id } })

// 点赞
export const like   = (articleId) => request({ url: '/like', method: 'POST', data: { article_id: articleId } })
export const unlike = (articleId) => request({ url: '/unlike', method: 'POST', data: { article_id: articleId } })

// 我的
export const myArticles = () => request({ url: '/me/articles' })
export const myComments = () => request({ url: '/me/comments' })
export const myLikes    = () => request({ url: '/me/likes' })