# 接口速查（ThinkPHP 8 · 22 个路径 / 23 个操作）

Base URL：

```
http://localhost/mymessage/tp8/public/index.php
http://localhost:8000            （php think run）
```

## 统一返回

```json
{ "code": 0, "msg": "ok", "data": {} }
```

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

## 鉴权（两种都支持）

| 方式 | 用法 | 适用 |
|---|---|---|
| **Token** | 登录/注册返回 `data.token`，请求头加 `X-Token: <token>` | 手机端 / 小程序 / App |
| **Session** | 登录后浏览器自动存 `PHPSESSID` | 浏览器后台 |

后端先查 Session，没有再查 Token。

## 认证

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| POST | `/api/register` | 公开 | username, password, password2 |
| POST | `/api/login` | 公开 | username, password |
| POST | `/api/logout` | 登录 | — |
| GET | `/api/me` | 登录 | — |

## 文章

| 方法 | 路径 | 权限 | 参数 |
|---|---|---|---|
| GET | `/api/articles` | 公开 | page, size, random |
| GET | `/api/article` | 公开 | id |
| POST | `/api/article` | 登录 | title, content, status |
| POST | `/api/article/delete` | 登录 | id |

> `status=1` 只有管理员有效（直接发布）；普通用户传了也按待审核处理。

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
| POST | `/api/admin/article/update` | 管理员 | id, title, content |
| POST | `/api/admin/comment/delete` | 管理员 | id |
| GET | `/api/admin/comments` | 管理员 | page, size, keyword |
| GET | `/api/admin/users` | 管理员 | page, size, keyword |

## 测试顺序

```bash
# 1. 登录，拿 token
curl -X POST ".../api/login" -d "username=admin&password=123456"

# 2. 带 token 查当前用户
curl -H "X-Token: <token>" ".../api/me"

# 3. 管理员直接发一篇已发布
curl -H "X-Token: <token>" -X POST ".../api/article" \
     -d "title=直发测试&content=正文内容&status=1"

# 4. 前台列表应立刻看到
curl ".../api/articles?page=1"

# 5. 点赞 → 重复点赞返回 1004
curl -H "X-Token: <token>" -X POST ".../api/like" -d "article_id=61"
curl -H "X-Token: <token>" -X POST ".../api/like" -d "article_id=61"

# 6. 发评论
curl -H "X-Token: <token>" -X POST ".../api/comment" -d "article_id=61&content=测试留言"

# 7. 后台改标题，再看前台
curl -H "X-Token: <token>" -X POST ".../api/admin/article/update" \
     -d "id=61&title=改后的标题&content=改后的正文"
```

## 演示账号

| 账号 | 密码 | 角色 |
|---|---|---|
| admin | 123456 | 管理员 |
| stu01 ~ stu10 | 123456 | 普通用户 |

## 注意

- POST 接口不能直接在浏览器打开，会返回 `{"code":404,"msg":"接口不存在或请求方法不对"}`
- 点赞用 `article_likes` 表，有 `(article_id, user_id)` 唯一索引，重复点赞必须先判断