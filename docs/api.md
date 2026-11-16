# 接口速查（21 个）

| 方法 | 路径 | 分组 | 说明 |
|---|---|---|---|
 | POST | `/api/register.php` | 认证 | 注册 |
 | POST | `/api/login.php` | 认证 | 登录 |
 | POST | `/api/logout.php` | 认证 | 退出登录 |
 | GET | `/api/me.php` | 认证 | 当前登录用户 |
 | GET | `/api/articles.php` | 文章 | 文章列表（公开） |
 | GET | `/api/article_comment.php` | 文章 | 文章详情 + 评论 |
 | POST | `/api/article_add.php` | 文章 | 发布文章 |
 | POST | `/api/articles_delete.php` | 文章 | 删除文章 |
 | GET | `/api/comments.php` | 评论 | 某篇文章的评论列表 |
 | POST | `/api/comments_add.php` | 评论 | 发表评论 |
 | POST | `/api/comments_delete.php` | 评论 | 删除评论 |
 | POST | `/api/likes.php` | 点赞 | 点赞 |
 | POST | `/api/unlikes.php` | 点赞 | 取消点赞 |
 | GET | `/api/me_articles.php` | 我的 | 我的文章 |
 | GET | `/api/me_comments.php` | 我的 | 我的评论 |
 | GET | `/api/me_likes.php` | 我的 | 我的点赞 |
 | GET | `/api/admin/articles.php` | 管理端 | 文章列表（后台） |
 | GET | `/api/admin/article.php` | 管理端 | 文章详情（后台） |
 | POST | `/api/admin/articles_audit.php` | 管理端 | 审核文章 |
 | POST | `/api/admin/comment_delete.php` | 管理端 | 删除评论（管理员） |
 | GET | `/api/admin/users.php` | 管理端 | 用户列表 |
 
## 测试顺序

1. 注册 → 2. 登录（Apifox 自动存 Cookie）→ 3. me
4. 发布文章 → 5. 文章列表（看不到，因为待审核）
6. 用管理员审核（status=1）→ 7. 文章列表（出现）
8. 点赞 → 9. 重复点赞（1004）→ 10. 取消点赞
11. 发表评论 → 12. 评论列表 → 13. 文章详情
14. 我的文章 / 我的评论 / 我的点赞
15. 管理端用户列表

## 错误码

| code | 含义 |
|---|---|
| 0 | 成功 |
| 1 | 参数错误 |
| 401 | 未登录 |
| 403 | 无权限 |
| 404 | 不存在 |
| 1001 | 用户名或密码错误 |
| 1002 | 用户名已存在 |
| 1004 | 已经点过赞 |
| 1005 | 只能删除自己的内容 |