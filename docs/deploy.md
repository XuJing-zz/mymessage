# 部署到宝塔（公网可访问）

> 目标：云服务器装宝塔 → 跑起 ThinkPHP → 至少再部署一端前端 → 手机流量能打开。
>
> 本文对应项目：`tp8/`（后端）、`frontend/`（Vue 后台）、`mobile/`（uni-app 手机端）。

---

## 0. 准备清单

| 项 | 要求 |
|---|---|
| 云服务器 | 2 核 2G 起，Linux（Ubuntu 22.04 / CentOS 7 / Debian 12） |
| 安全组放行 | 22、80、443、8888（宝塔面板） |
| 域名 | 可选。**大陆服务器绑域名用 80/443 必须 ICP 备案**；不备案就用 IP 访问 |
| 本地环境 | 能 `git clone`、能 `npm run build` |

> **不想备案**：直接 `http://公网IP/api/...`，跳过 HTTPS 和域名部分。
> **想要 HTTPS + 域名**：买香港/境外节点，或大陆机器买满 3 个月去备案。

---

## 1. 装宝塔面板

```bash
ssh root@你的公网IP

# 安装命令以宝塔官网最新为准：https://www.bt.cn/new/download.html
curl -sSO https://download.bt.cn/install/install_lts.sh
bash install_lts.sh
```

装完会打印：

```
外网面板地址: http://公网IP:8888/xxxx
username: xxxx
password: xxxx
```

**登录后立刻做：**

1. 绑定宝塔账号
2. 面板设置 → 改端口（如 28888）→ 安全组同步放行
3. 面板设置 → 开启「面板 SSL」（可选）

---

## 2. 装运行环境

**软件商店 → 安装：**

| 软件 | 版本 | 备注 |
|---|---|---|
| Nginx | 1.22+ | |
| MySQL | **5.7** | 与本地一致 |
| PHP | **8.0** | 与本地 8.0.30 对齐 |
| phpMyAdmin | 最新 | 可选，方便导库 |

**PHP 8.0 → 设置：**

- **安装扩展**：确认 `mysqli`、`mbstring`、`fileinfo`、`opcache`
- **禁用函数**：删掉 `putenv`、`proc_open`（ThinkPHP 需要，不删会报错）

---

## 3. 放行端口

两处都要动：

### ① 云厂商安全组

```
22    SSH
80    HTTP
443   HTTPS
8888  宝塔面板（改端口后放新端口）
```

### ② 宝塔防火墙

```
面板 → 安全 → 放行 80、443
```

> 数据库 3306 **不建议**对公网开放。如果确实需要远程连库：
> 安全组放行 + 宝塔放行 + MySQL 只授权指定 IP，并设强密码。

---

## 4. 建数据库并导入本地数据

**宝塔 → 数据库 → 添加数据库：**

```
数据库名: message_board
用户名:   message_board
密码:     强密码（记下来）
字符集:   utf8mb4
```

### 导出本地数据

**本地（Windows）命令行：**

```cmd
"D:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysqldump.exe" ^
  --host=127.0.0.1 --user=user_01 --password=123456 ^
  --default-character-set=utf8mb4 --databases message_board ^
  > D:\mymessage.sql
```

> 导出后打开看一眼前几行，确认是 `CREATE TABLE` 而不是报错信息。

### 导入线上

**方式 A：phpMyAdmin（简单）**

```
phpMyAdmin → 选 message_board → 导入 → 选 mymessage.sql → 执行
```

**方式 B：命令行**

```bash
mysql -u message_board -p message_board < /tmp/mymessage.sql
```

### 只想建空表（不导数据）

用 `README.md` 里「5. 数据表」的四条 `CREATE TABLE` 即可。

---

## 5. 上传代码

```bash
cd /www/wwwroot
yum install -y git        # CentOS
# apt install -y git      # Ubuntu/Debian

git clone https://github.com/XuJing-zz/mymessage.git
```

私有仓库用 token：

```bash
git clone https://<用户名>:<token>@github.com/XuJing-zz/mymessage.git
```

目录结构：

```
/www/wwwroot/mymessage/
├─ tp8/
├─ frontend/
├─ mobile/
└─ docs/
```

---

## 6. 建站点（关键：运行目录指到 public）

**宝塔 → 网站 → 添加站点：**

| 字段 | 值 |
|---|---|
| 域名 | `你的域名` 或 `公网IP` |
| 根目录 | `/www/wwwroot/mymessage/tp8/public` |
| PHP 版本 | **8.0** |

**⚠️ 最容易错的一步：根目录必须是 `tp8/public`，不是 `tp8`。**
如果填错了，可以在站点设置 →「网站目录」→「运行目录」里改成 `/public`。

---

## 7. 配置 .env（连线上库 + 关调试）

**宝塔 → 文件 → `tp8/` → 新建 `.env`：**

```ini
APP_DEBUG = false

DEFAULT_TIMEZONE = Asia/Shanghai

DB_TYPE = mysql
DB_HOST = 127.0.0.1
DB_NAME = message_board
DB_USER = message_board
DB_PASS = 你的强密码
DB_PORT = 3306
DB_CHARSET = utf8mb4

DEFAULT_LANG = zh-cn
```

**生产环境必须 `APP_DEBUG = false`**，否则报错页会暴露路径、SQL、文件结构。

---

## 8. 装依赖 + 目录权限

```bash
cd /www/wwwroot/mymessage/tp8

# Composer（如未安装）
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# 生产模式安装依赖（不带 dev 包）
composer install --no-dev --optimize-autoloader
```

**权限：**

```bash
chown -R www:www /www/wwwroot/mymessage
chmod -R 755 /www/wwwroot/mymessage/tp8
chmod -R 775 /www/wwwroot/mymessage/tp8/runtime
rm -rf /www/wwwroot/mymessage/tp8/runtime/*
```

---

## 9. 伪静态（ThinkPHP 路由）

**站点设置 → 伪静态**，整段替换为：

```nginx
# 接口：/api/xxx → index.php
location /api/ {
    try_files $uri $uri/ /index.php?$query_string;
}

# 其余请求 → index.php
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
    }
}
```

保存 → 重载 Nginx。

**验证：**

```bash
curl http://你的域名/api/articles?page=1
```

应返回 JSON。若返回 404 / 下载 php 文件 / 502，见文末排查表。

---

## 10. 部署 Vue 后台

**本地构建：**

```bash
cd D:\phpstudy_pro\WWW\mymessage\frontend
npm run build
```

**上传 `dist/` 到服务器：**

```
/www/wwwroot/mymessage/tp8/public/admin/
```

**Nginx 增加（站点设置 → 配置文件，放在 server 块内）：**

```nginx
location /admin/ {
    alias /www/wwwroot/mymessage/tp8/public/admin/;
    try_files $uri $uri/ /admin/index.html;
}
```

**访问：** `http://你的域名/admin/`

> 前端 `baseURL` 是 `/api`，和接口同域，**不用改代码**。
> 登录态走 Cookie（同源）+ 后端也支持 Token，二者都行。

---

## 11. 部署手机端 H5

**HBuilderX → 发行 → 网站-PC/手机 H5**，产物在 `unpackage/dist/build/h5`。

**上传到：**

```
/www/wwwroot/mymessage/tp8/public/mobile/
```

**Nginx：**

```nginx
location /mobile/ {
    alias /www/wwwroot/mymessage/tp8/public/mobile/;
    try_files $uri $uri/ /mobile/index.html;
}
```

### 接口地址（重要）

`mobile/service/request.js` 已经做成**自动判断**：

```js
// 本地（localhost / 127.0.0.1）→ 走本机后端
// 线上（其他域名/IP）      → 走 同域 /api
```

**所以 H5 部署到站点后不用改代码。**

如果部署到**不同域名**，把文件里的 `PROD_BASE` 改成你的完整接口地址：

```js
const PROD_BASE = 'https://api.你的域名.com/api'
```

### 小程序（加分项）

```
HBuilderX → 发行 → 小程序-微信 → 上传
微信公众平台 → 开发设置 → 服务器域名 → 填 https://你的域名
```

⚠️ 小程序必须 HTTPS。

---

## 12. HTTPS（有域名就做）

```
站点设置 → SSL → Let's Encrypt → 申请 → 开启「强制 HTTPS」
```

申请前确认域名已解析到本机 IP，且 80 端口可从外网访问。

---

## 13. 验收自测（照这个顺序）

```
① 手机用 4G/5G（关 WiFi）打开：
   http://你的域名/api/articles?page=1
   → 应看到 JSON

② 打开 http://你的域名/admin/
   → 能看到登录页

③ 用 admin / 123456 登录
   → 能进后台、能看到文章列表

④ 后台发一篇（管理员可直接发布）
   → 回列表能看到

⑤ 打开 http://你的域名/mobile/
   → 能看文章列表、能登录、能点赞

⑥ 手机端点赞后，后台文章列表的点赞数变了
```

**验收要求的就是：登录 + 看文章/发文章，且用手机流量能打开。**

---

## 14. 排查表

| 现象 | 原因 | 解决 |
|---|---|---|
| **502 Bad Gateway** | PHP-FPM 未启动 / 版本不对 | 站点设置确认 PHP 8.0；重启 PHP |
| **500 空白页** | 代码报错但调试关了 | 临时 `APP_DEBUG=true`，看 `runtime/log` |
| **404 所有接口** | 伪静态没配 | 按第 9 节配置 |
| **下载 index.php** | PHP 未解析 | 站点 PHP 版本未选 / Nginx 配置错 |
| **数据库连接失败** | `.env` 账号密码错 / 库不存在 | 对照宝塔数据库页面 |
| **Class not found** | 没执行 `composer install` | 进 `tp8` 目录安装 |
| **权限不足 / 写入失败** | runtime 不可写 | `chmod -R 775 runtime`，属主 `www` |
| **跨域报错** | 前端和接口不同域且未开 CORS | 后端已内置 `AllowCrossDomain`；确认请求带 `Origin` |
| **登录后立刻掉线** | Cookie 被拦 / 跨站 | 手机端用 `X-Token`；或前后端同域 |
| **手机端连不上** | 接口地址写死 127.0.0.1 | 确认已部署 H5 版（自动走同域） |

---

## 15. 生产环境收尾

```
① APP_DEBUG = false                    ✅ 已做
② 删掉测试数据（可选）
③ 修改演示账号密码（admin/123456 必须改）
④ 宝塔计划任务：每天备份数据库
⑤ 面板端口改掉 + 开防火墙
⑥ SSH 改端口 + 密钥登录
⑦ 关掉 3306 公网访问
```

**改管理员密码（线上）：**

```sql
UPDATE `user` SET password = '新密码' WHERE username = 'admin';
```

> 当前后端密码是明文存储（课程演示用）。正式上线建议改回 `password_hash` + `password_verify`，代码位置：`tp8/app/controller/Auth.php`。
