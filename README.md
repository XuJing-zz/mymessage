# mymessage · 博客系统

一个前后端分离的博客项目：**ThinkPHP 8 提供 JSON 接口，Vue 3 提供管理后台**。

- 后台支持文章审核、编辑、评论管理、用户列表
- 前台支持浏览文章、点赞、评论、个人中心
- 接口文档为标准 OpenAPI 3.0，可直接导入 Apifox

---

## 一、技术栈

| 层 | 技术 |
|---|---|
| 后端 | PHP 8.0.30 · ThinkPHP 8.1.4 · MySQL 5.7 |
| 前端 | Vue 3 · Vite · vue-router 4 · axios |
| 服务器 | Nginx 1.15（phpStudy 集成环境） |
| 文档 | OpenAPI 3.0.1（Apifox / Swagger 通用） |
| 版本管理 | Git + GitHub |

---

## 二、目录结构

```
mymessage/
├─ tp8/                      后端（ThinkPHP 8）
│  ├─ app/
│  │  ├─ controller/         7 个控制器，22 个接口方法
│  │  ├─ middleware.php      全局中间件（已开启 SessionInit）
│  │  └─ ExceptionHandle.php 异常处理
│  ├─ route/app.php          23 条路由
│  ├─ config/                路由、会话、数据库配置
│  ├─ public/index.php       入口文件
│  └─ .env                   数据库配置（不提交）
│
├─ frontend/                 前端（Vue 3 + Vite）
│  ├─ src/
│  │  ├─ api/                接口封装（request + 6 个模块）
│  │  ├─ stores/auth.js      登录状态
│  │  ├─ router/index.js     7 条路由 + 登录守卫
│  │  ├─ components/         文章卡片、分页、状态标签、评论列表
│  │  └─ views/              登录、注册、首页、详情、我的、后台
│  ├─ vite.config.js         端口 5174 + /api 代理
│  └─ package.json
│
└─ docs/
   ├─ apifox-openapi.json    接口文档（OpenAPI 3.0，21 个接口）
   ├─ api.md                 接口速查表
   └─ mysqli-glossary.md     mysqli 中英对照词表
```

---

## 三、快速开始

### 1. 环境要求

```
PHP   >= 8.0（扩展：mysqli、mbstring、json、session）
MySQL >= 5.7
Node  >= 18
```

本项目使用 phpStudy 集成环境：

```
PHP    D:\phpstudy_pro\Extensions\php\php8.0.30nts
MySQL  D:\phpstudy_pro\Extensions\MySQL5.7.26
Nginx  D:\phpstudy_pro\Extensions\Nginx1.15.11
网站根  D:\phpstudy_pro\WWW
```

### 2. 启动服务

```
① 打开 phpStudy 面板 → 启动 Nginx、MySQL
② 确认 PHP-CGI 监听 9000 端口
③ 启动前端：
   cd D:\phpstudy_pro\WWW\mymessage\frontend
   npm install
   npm run dev
```

### 3. 访问

| 用途 | 地址 |
|---|---|
| 前端页面 | http://localhost:5174 |
| 后端根地址 | http://localhost/mymessage/tp8/public/index.php |
| 接口示例 | http://localhost/mymessage/tp8/public/index.php/api/articles |

### 4. 数据库配置

复制 `.env` 模板并填写（`.env` 不进版本库）：

```ini
APP_DEBUG = true
DEFAULT_TIMEZONE = Asia/Shanghai

DB_TYPE = mysql
DB_HOST = 127.0.0.1
DB_NAME = message_board
DB_USER = user_01
DB_PASS = 123456
DB_PORT = 3306
DB_CHARSET = utf8mb4
```

> ⚠️ ThinkPHP 8 使用 `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS`；
> ThinkPHP 6 的 `[DATABASE]` 分段写法在 8 中不生效。

### 5. 数据表

```sql
CREATE DATABASE IF NOT EXISTS message_board DEFAULT CHARSET utf8mb4;

CREATE TABLE `user` (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role TINYINT NOT NULL DEFAULT 0,           -- 0 普通用户 / 1 管理员
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE article (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  status TINYINT NOT NULL DEFAULT 0,         -- 0 待审核 / 1 已通过 / 2 已驳回
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_status (status),
  CONSTRAINT fk_article_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE comment (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_article (article_id),
  CONSTRAINT fk_comment_article FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE,
  CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE article_likes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_like_article_user (article_id, user_id),
  CONSTRAINT fk_like_article FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE,
  CONSTRAINT fk_like_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 四、测试账号

| 角色 | 账号 | 密码 | 说明 |
|---|---|---|---|
| 管理员 | `admin` | `123456` | role=1，可进后台 |
| 普通用户 | `stu01` ~ `stu10` | `123456` | role=0 |

> 演示数据：10 个用户 × 5 篇文章 = 50 篇，每篇 12 条评论、11 个点赞。
> 每人 4 篇已通过、1 篇待审核，用于演示后台审核流程。

**把普通用户提为管理员：**

```sql
UPDATE `user` SET role = 1 WHERE username = 'stu01';
```

> 改完必须**退出重新登录**，role 存在 Session 里。

---

## 五、接口文档（Apifox 项目入口）

### 入口

```
docs/apifox-openapi.json      OpenAPI 3.0.1，21 个接口，66 KB
```

### 导入步骤

1. 打开 Apifox，新建或进入项目
2. **项目设置 → 导入数据 → OpenAPI / Swagger**
3. 选择本仓库的 `docs/apifox-openapi.json`
4. 导入模式选 **覆盖**（同路径接口会被更新，不产生重复）
5. 点确定

导入后左侧出现 6 个分组：

```
认证    POST /api/register   POST /api/login   POST /api/logout   GET /api/me
文章    GET  /api/articles   GET  /api/article  POST /api/article  POST /api/article/delete
评论    GET  /api/comments   POST /api/comment  POST /api/comment/delete
点赞    POST /api/like       POST /api/unlike
我的    GET  /api/me/articles  GET /api/me/comments  GET /api/me/likes
管理端  GET  /api/admin/articles      GET  /api/admin/article
        POST /api/admin/article/audit POST /api/admin/article/update
        POST /api/admin/comment/delete
        GET  /api/admin/comments      GET /api/admin/users
```

### 服务地址

```
http://localhost/mymessage/tp8/public/index.php
```

### 测试顺序

```
1. POST /api/register      注册并自动登录
2. GET  /api/me            确认登录态（Cookie 生效）
3. POST /api/article       发文章（status=0 待审核）
4. 管理员登录 → POST /api/admin/article/audit  审核通过
5. GET  /api/articles      能看到新文章
6. POST /api/like          点赞；再点一次应返回 1004
7. POST /api/comment       发评论
8. GET  /api/article?id=x  文章 + 评论 + 是否已赞
```

> ⚠️ POST 接口不能直接粘贴到浏览器地址栏，会返回
> `{"code":404,"msg":"接口不存在或请求方法不对（当前 GET）"}`。

---

## 六、接口约定

### 统一返回格式

```json
{ "code": 0, "msg": "ok", "data": {} }
```

### 返回码

| code | 含义 |
|---|---|
| 0 | 成功 |
| 1 | 参数错误 |
| 401 | 未登录 |
| 403 | 无权限 |
| 404 | 资源不存在 |
| 1001 | 用户名或密码错误 |
| 1002 | 用户名已存在 |
| 1004 | 已经点过赞 |
| 1005 | 只能删除自己的内容 |

### 鉴权方式

Session Cookie：

```
登录成功 → 服务端写 Session，响应头下发 PHPSESSID
后续请求 → 浏览器自动带 Cookie → 服务端读出当前用户
```

**身份只来自服务端 Session，绝不接受前端传入的 user_id。**

### 分页参数

```
?page=1&size=5      page 从 1 开始，size 默认 5（文章）/ 20（后台列表），上限 50
```

---

## 七、功能清单

### 前台

- 文章列表（只显示已审核，分页）
- 文章详情（正文 + 评论列表 + 点赞状态）
- 注册 / 登录 / 退出
- 点赞 / 取消点赞（同一文章只能赞一次）
- 发表评论 / 删除自己的评论
- 我的文章 / 我的评论 / 我的点赞

### 后台（需管理员）

- 文章列表（按状态筛选、标题搜索）
- 文章详情（任意状态）
- 审核文章（通过 / 驳回）
- **编辑文章标题与正文**
- 删除评论（任意人的）
- 留言列表（关键词搜索）
- 用户列表（不返回密码）

---

## 八、开发说明

### 后端分层

```
route/app.php         路由
      ↓
controller/*.php      参数校验、权限判断，不直接查库
      ↓
app/Article.php 等    模型（extends think\Model），唯一接触数据库的层
```

控制器中不出现 `Db::`，查询统一写在模型里。

### 前端请求链

```
页面 → stores/auth.js → api/*.js → axios(request.js) → /api/*
                                        ↓
                            Vite 代理 → ThinkPHP 真实地址
```

`request.js` 做了两件事：

- `withCredentials: true`：带上 Session Cookie
- 响应拦截器：`code === 0` 返回 `data`，否则抛出带 `msg` 的错误

### 关键配置

| 配置 | 位置 | 值 | 作用 |
|---|---|---|---|
| `SessionInit` | `app/middleware.php` | 开启 | 不开则登录态无法保持 |
| `url_route_must` | `config/route.php` | true | 未匹配路由返回 404 JSON |
| `route_complete_match` | `config/route.php` | true | 避免 `/api/me` 吞掉 `/api/me/articles` |
| 代理 | `frontend/vite.config.js` | `/api` → tp8 | 开发期同源，避免跨域 |

---

## 九、常见问题

| 现象 | 原因 | 解决 |
|---|---|---|
| **502 Bad Gateway** | PHP-CGI（9000）未运行 | phpStudy 面板启动 PHP |
| **数据库连接失败** | MySQL 未启动，或 `.env` 变量名写成 TP6 格式 | 启动 MySQL；改用 `DB_*` |
| **登录成功但立刻 401** | `SessionInit` 中间件被注释 | 打开 `app/middleware.php` |
| **POST 接口 404** | 用 GET 访问 | 改用 POST（Apifox / curl / 前端） |
| **`/api/me/articles` 返回 me 的数据** | 路由前缀匹配 | `route_complete_match = true` |
| **重复点赞报 500** | 未做去重检查，撞唯一索引 | 先查 `article_likes` 是否存在 |
| **前端每页条数不变** | 前端写死了 `size` | 去掉 `size` 参数，用后端默认值 |
| **页面 401 循环** | 前端代理未配置 | 检查 `vite.config.js` 的 proxy |

---

## 十、说明

本项目用于课程实践，密码按演示要求以**明文**存储（`user.password`）。

生产环境必须改为：

```php
password_hash($password, PASSWORD_DEFAULT)     // 写入
password_verify($input, $user['password'])     // 校验
```

同时应补充：CSRF 防护、登录失败次数限制、接口频率限制、HTTPS。
