# 接口速查（ThinkPHP 8 · 21 个）

Base URL：`http://localhost/mymessage/tp8/public/index.php`

> 统一返回 `{ code, msg, data }`，`code=0` 成功。
> 错误码：0 成功 / 1 参数错误 / 401 未登录 / 403 无权限 / 404 不存在 / 1001 密码错误 / 1002 用户名存在 / 1004 已点赞 / 1005 只能删自己的。

## 认证

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| POST | `/api/register` | 公开 | username, password |
| POST | `/api/login` | 公开 | username, password |
| POST | `/api/logout` | 公开 | — |
| GET | `/api/me` | 登录 | — |

## 文章

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| GET | `/api/articles` | 公开 | page, size |
| GET | `/api/article` | 公开 | id |
| POST | `/api/article` | 登录 | title, content |
| POST | `/api/article/delete` | 登录 | id |

## 评论

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| GET | `/api/comments` | 公开 | article_id |
| POST | `/api/comment` | 登录 | article_id, content |
| POST | `/api/comment/delete` | 登录 | id |

## 点赞

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| POST | `/api/like` | 登录 | article_id |
| POST | `/api/unlike` | 登录 | article_id |

## 我的

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| GET | `/api/me/articles` | 登录 | page, size |
| GET | `/api/me/comments` | 登录 | page, size |
| GET | `/api/me/likes` | 登录 | page, size |

## 管理端

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| GET | `/api/admin/articles` | 管理员 | page, size, status, keyword |
| GET | `/api/admin/article` | 管理员 | id |
| POST | `/api/admin/article/audit` | 管理员 | id, status(1/2) |
| POST | `/api/admin/comment/delete` | 管理员 | id |
| GET | `/api/admin/comments` | 管理员 | page, size, keyword |
| GET | `/api/admin/users` | 管理员 | page, size, keyword |

## 测试顺序

```
1. POST /api/register        username=demo1 password=123456
2. POST /api/login           ← Apifox 自动保存 Cookie
3. GET  /api/me
4. POST /api/article         title=测试 content=内容
5. GET  /api/articles        → total 0（待审核）
6. 管理员账号登录 → POST /api/admin/article/audit  id=1&status=1
7. GET  /api/articles        → total 1
8. POST /api/like            article_id=1
9. POST /api/like            → 1004
10. POST /api/comment        article_id=1&content=好用
11. GET  /api/article?id=1   → article + comments
12. GET  /api/me/articles    → status_text 已通过
13. GET  /api/admin/users    → role_text
```

## 注意

- **POST 接口不能用浏览器打开**，会返回 `{"code":404,"msg":"接口不存在或请求方法不对（当前 GET）"}`
- 管理员账号需要先把 `user.role` 改成 1 并**重新登录**才会生效