# mymessage · 博客系统

一个前后端分离的博客项目：**ThinkPHP 8 提供 JSON 接口，Vue 3 提供管理后台**。

- 后台支持文章审核、编辑、评论管理、用户列表
- 前台支持浏览文章、点赞、评论、个人中心
- 接口文档为标准 OpenAPI 3.0，可直接导入 Apifox

---

### 线上演示（公网）

| 端 | 地址 |
|---|---|
| 手机端 H5 | http://43.143.237.166/h5/ |
| 后端接口 | http://43.143.237.166/api/articles?page=1 |
| 宝塔面板 | https://43.143.237.166:8888/tencentcloud |

### 本地跑起来（Windows + phpStudy）

# 1. 起 MySQL
# 2. 起 Nginx
# 3. 起 PHP-CGI（必须带 -b，否则 502）
Start-Process "D:\phpstudy_pro\Extensions\php\php8.0.30nts\php-cgi.exe" `
  -ArgumentList "-b","127.0.0.1:9000" -WindowStyle Hidden `
  -Environment @{ PHP_FCGI_CHILDREN="4"; PHP_FCGI_MAX_REQUESTS="500" }

# 4. 起前端
cd D:\phpstudy_pro\WWW\mymessage\frontend
npm run dev
```

- 前端：http://localhost:5174
- 后端：http://localhost/mymessage/tp8/public/index.php/api/articles
- 或者用内置服务器：`cd tp8 && php think run`（8000 端口）

### Apifox 在哪

```
docs/apifox-openapi.json      ← 导入这个文件
```

导入步骤见[十二、接口文档与 Apifox](#十二接口文档与-apifox)。

### 测试账号

| 角色 | 账号 | 密码 |
|---|---|---|
| 管理员 | `admin` | `123456` |
| 测试账号 | `user01` | `123456` |

### 部署到公网

完整步骤见 **[docs/deploy.md](docs/deploy.md)**（宝塔：装环境 → 建站 → 建库导数据 → 伪静态 → 传前端 → 关调试）。

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
│  │  ├─ middleware.php      全局中间件
│  │  └─ ExceptionHandle.php 异常处理
│  ├─ route/app.php          23 条路由
│  ├─ config/                路由、会话、数据库配置
│  ├─ public/index.php       入口文件
│  └─ .env                   数据库配置
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
├─ mobile/                   手机端（uni-app，HBuilderX 项目）
│  ├─ pages/                 8 个页面（列表 / 详情 / 登录 / 发布 / 我的 / 审核 / 留言 / 用户）
│  ├─ service/               接口封装（uni.request + X-Token）
│  ├─ components/TabBar.vue  底部导航
│  └─ pages.json / manifest.json
│
└─ docs/
   ├─ apifox-openapi.json    接口文档（OpenAPI 3.0，22 个路径）
   ├─ api.md                 接口速查表
   ├─ deploy.md              宝塔部署手册
   └─ mysqli-glossary.md     mysqli 中英对照词表
```

---

> 部署到公网（宝塔）：见 [docs/deploy.md](docs/deploy.md)

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

## 四、接口约定

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

## 五、功能清单

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



## 六、说明（安全）

密码已从明文升级为哈希（`tp8/app/controller/Auth.php`）：

- 注册：`password_hash($password, PASSWORD_DEFAULT)`
- 登录：先 `password_verify()`；对历史明文账号比对成功后**自动升级**为哈希

仍需在生产环境补充：CSRF 防护、登录失败次数限制、接口频率限制、HTTPS。

---

## 七、UniApp 客户端（H5 + 微信小程序）

前端除 Vue 后台外，另有一套 uni-app 客户端，目录 `uniapp/`，编译目标：

### 接口地址

`service/request.js` 按环境自动切换：

- 本地：`http://127.0.0.1/mymessage/tp8/public/index.php/api`
- 线上 H5：`location.origin + '/api'`
- 登录态：`X-Token` 请求头（后端 `ApiBase::currentUser()` 兼容 Session 与 X-Token）

---

## 八、线上部署（宝塔面板）

| 项 | 值 |
|---|---|
| 访问地址 | `http://43.143.237.166/` → 302 → `/h5/` |
| 服务器 | OpenCloudOS 9.6 · Nginx 1.30.7 · PHP 8.1.35 · MySQL 5.7.44 |
| 站点根目录 | `/www/wwwroot/mymessage/public` |
| 数据库 | `message_board`（utf8mb4）· 账号 `mymessage` |
| 面板 | `http://43.143.237.166:8888/tencentcloud` |





## 九、测试账号

| 角色 | 账号 | 密码 | 说明 |
|---|---|---|---|
| 管理员 | `admin` | `123456` | 审核文章、管理留言与用户 |
| 普通用户 | `user01` | `123456` | 发文章、点赞、评论 |
| 普通用户 | `tp8test` | `123456` | 接口测试账号 |


> 线上与本地是同一套数据（2026-11-24 导出后导入）。

---

## 十、接口文档与 Apifox

- 文件：`docs/apifox-openapi.json`（OpenAPI 3.0，21 个接口）
- 本地环境：`http://127.0.0.1/mymessage/tp8/public/index.php/api`
- **线上环境**：`http://43.143.237.166/api`

### Apifox 里加「线上」环境

1. Apifox → 项目设置 → 环境管理 → 新建环境，命名 `线上`
2. 新增变量 `baseUrl` = `http://43.143.237.166/api`
3. 接口里把 URL 改成 `{{baseUrl}}/articles`

---

## 十一、Navicat 连接线上 MySQL

| 项 | 值 |
|---|---|
| 主机 | `43.143.237.166` |
| 端口 | `3306` |
| 数据库 | `message_board` |
| 用户名 | `mymsg_remote` |
| 密码 | `Mymsg2026At_Remote` |



## 十二、结业验收清单

- [x] 后端只用 `think\Model`，业务代码无 `Db::`（`grep -r "Db::" tp8/app` 为空）
- [x] 控制器 / 模型 / 接口 / 前端分工清楚（见「二、目录结构」）
- [x] 点赞必须登录；重复点赞返回 `code=1004`（`article_likes` 唯一索引兜底）
- [x] 管理后台：文章审核（看正文）、删除文章、留言按文章分组、用户列表
- [x] 手机端：登录、列表、详情、点赞、留言
- [x] 公网可访问：`http://43.143.237.166/`
- [x] Apifox：`docs/apifox-openapi.json`，本地 22 项自动化通过
- [x] Navicat 连线上库：**等安全组放行 3306**
- [x] Git：本地提交 + 标签（远端推送受网络限制，见下）

---

## 十五、Git

本地仓库：`D:\phpstudy_pro\WWW\mymessage`（remote `https://github.com/XuJing-zz/mymessage.git`）


