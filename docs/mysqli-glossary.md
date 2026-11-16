# mysqli 中英对照表（扫 PHP 手册整理）

> 手册里 mysqli 有 **200+ 个函数**，但一个 CRUD 项目实际只用 **12 个**。
> 先把这 12 个练到条件反射，其余用到再查。

---

## 一、必会 12 个（你的项目全都在用）

| 函数 | 拆开看 | 中文 | 用在哪 |
|---|---|---|---|
| `mysqli_connect()` | mysqli + connect | 连接数据库 | `db.php` |
| `mysqli_set_charset()` | set + charset | 设置字符集 | `db.php` |
| `mysqli_prepare()` | prepare | 预编译 SQL | 所有查询 |
| `mysqli_stmt_bind_param()` | stmt + bind + param | 绑定参数 | 所有查询 |
| `mysqli_stmt_execute()` | execute | 执行语句 | 所有查询 |
| `mysqli_stmt_get_result()` | get + result | 取结果集 | 所有 SELECT |
| `mysqli_fetch_assoc()` | fetch + assoc | 取一行（关联数组） | 遍历结果 |
| `mysqli_insert_id()` | insert + id | 上次插入的自增 ID | 注册、发文 |
| `mysqli_stmt_affected_rows()` | affected + rows | 受影响行数 | 改/删后判断 |
| `mysqli_error()` | error | 连接上的错误 | 调试 |
| `mysqli_stmt_error()` | stmt + error | 语句上的错误 | 调试 |
| `mysqli_close()` | close | 关闭连接 | 收尾（可省） |

### 五步固定流程

```php
$stmt = mysqli_prepare($connect, 'SELECT * FROM `user` WHERE id = ?');   // ① 预编译
mysqli_stmt_bind_param($stmt, 'i', $id);                                  // ② 绑定参数
mysqli_stmt_execute($stmt);                                               // ③ 执行
$res = mysqli_stmt_get_result($stmt);                                     // ④ 取结果集
while ($row = mysqli_fetch_assoc($res)) { ... }                           // ⑤ 一行一行取
```

**翻译：准备 → 绑值 → 执行 → 拿结果 → 逐行取。**

### 类型字符（bind_param 第一个参数）

| 字符 | 类型 | 例子 |
|---|---|---|
| `i` | integer 整数 | `$id` |
| `s` | string 字符串 | `$username` |
| `d` | double 浮点 | `$price` |
| `b` | blob 二进制 | 文件内容 |

**数量必须严格相等：3 个 `?` = 3 个字符 = 3 个变量。**

---

## 二、四个对象各管什么

| 对象 | 中文 | 负责 |
|---|---|---|
| `mysqli` | 连接 | 连库、执行、事务、字符集 |
| `mysqli_stmt` | 语句 | 预编译、绑参、执行、结果元数据 |
| `mysqli_result` | 结果集 | 游标、取行、行数、释放 |
| `mysqli_driver` | 驱动 | 错误报告模式（一般不用） |

---

## 三、按用途分类的常用函数

### 连接

| 函数 | 中文 |
|---|---|
| `mysqli_connect()` | 打开连接（返回连接对象） |
| `mysqli_real_connect()` | 打开连接（已有对象时用） |
| `mysqli_close()` | 关闭连接 |
| `mysqli_ping()` | 探活，断了尝试重连 |
| `mysqli_select_db()` | 切换数据库 |
| `mysqli_set_charset()` | 设置客户端字符集 |
| `mysqli_get_charset()` | 获取字符集信息 |
| `mysqli_options()` | 设置连接选项 |

### 查询

| 函数 | 中文 |
|---|---|
| `mysqli_query()` | 直接执行 SQL（无参数时可用） |
| `mysqli_real_query()` | 执行 SQL 并返回结果 |
| `mysqli_prepare()` | 预编译 SQL（防注入） |
| `mysqli_multi_query()` | 一次执行多条语句（少用） |
| `mysqli_store_result()` | 把结果集拉到客户端 |
| `mysqli_use_result()` | 逐行读结果（省内存） |

### 预处理语句（mysqli_stmt）

| 函数 | 中文 |
|---|---|
| `mysqli_stmt_prepare()` | 准备语句 |
| `mysqli_stmt_bind_param()` | 绑定参数到 `?` |
| `mysqli_stmt_bind_result()` | 绑定变量接收结果 |
| `mysqli_stmt_execute()` | 执行 |
| `mysqli_stmt_get_result()` | 取结果集 |
| `mysqli_stmt_fetch()` | 取一行到绑定变量 |
| `mysqli_stmt_affected_rows()` | 影响行数 |
| `mysqli_stmt_num_rows()` | 结果行数 |
| `mysqli_stmt_param_count()` | 参数个数（`?` 的数量） |
| `mysqli_stmt_data_seek()` | 跳到任意行 |
| `mysqli_stmt_reset()` | 重置语句，可重复执行 |
| `mysqli_stmt_free_result()` | 释放结果内存 |
| `mysqli_stmt_close()` | 关闭语句 |

### 结果集（mysqli_result）

| 函数 | 中文 |
|---|---|
| `mysqli_fetch_assoc()` | 取一行，关联数组（`$row['name']`） |
| `mysqli_fetch_array()` | 取一行，可选关联/索引/两者 |
| `mysqli_fetch_row()` | 取一行，索引数组（`$row[0]`） |
| `mysqli_fetch_object()` | 取一行，对象（`$row->name`） |
| `mysqli_fetch_all()` | 取全部（返回二维数组，需 mysqlnd） |
| `mysqli_fetch_column()` | 取一个字段值 |
| `mysqli_num_rows()` | 结果行数 |
| `mysqli_num_fields()` | 字段数量 |
| `mysqli_data_seek()` | 跳到任意行 |
| `mysqli_free_result()` | 释放结果内存 |

### 错误与状态

| 函数 | 中文 |
|---|---|
| `mysqli_error()` | 最近错误描述 |
| `mysqli_errno()` | 最近错误编号 |
| `mysqli_sqlstate()` | SQLSTATE 错误码 |
| `mysqli_connect_error()` | 连接失败原因 |
| `mysqli_connect_errno()` | 连接失败编号 |
| `mysqli_stmt_error()` | 语句错误描述 |
| `mysqli_stmt_errno()` | 语句错误编号 |
| `mysqli_warning_count()` | 警告数量 |

### 事务

| 函数 | 中文 |
|---|---|
| `mysqli_autocommit()` | 开关自动提交 |
| `mysqli_begin_transaction()` | 开始事务 |
| `mysqli_commit()` | 提交 |
| `mysqli_rollback()` | 回滚 |

### 信息

| 函数 | 中文 |
|---|---|
| `mysqli_insert_id()` | 上次 INSERT 的自增 ID |
| `mysqli_affected_rows()` | 影响行数 |
| `mysqli_info()` | 上次查询的附加信息 |
| `mysqli_get_server_info()` | 服务器版本 |
| `mysqli_get_client_info()` | 客户端版本 |
| `mysqli_thread_id()` | 当前连接线程 ID |

---

## 四、可以暂时忽略的（项目用不到）

| 类别 | 函数 | 为什么不用 |
|---|---|---|
| 别名/过时 | `mysqli_execute()`、`mysqli_escape_string()` | 官方说不要在新项目用 |
| mysqlnd 专属 | `mysqli_get_client_stats()`、`mysqli_stmt_get_result()` 的某些行为 | 依赖驱动 |
| 调试 | `mysqli_debug()`、`mysqli_dump_debug_info()` | 开发期才用 |
| 多语句 | `mysqli_multi_query()`、`mysqli_next_result()` | 有注入风险，一般禁用 |
| 服务器操作 | `mysqli_kill()`、`mysqli_refresh()`、`mysqli_stat()` | 运维用 |
| SSL | `mysqli_ssl_set()` | 本地不用 |
| 嵌入式 | `mysqli_embedded_server_start()` | 极罕见 |

---

## 五、读名字的通用规则

```
mysqli_stmt_bind_param
   ↑      ↑     ↑    ↑
  扩展   对象   动作  对象
```

**从右往左按 `_` 拆，每段都是一个认识的词。**

| 前缀/后缀 | 含义 |
|---|---|
| `mysqli_` | MySQLi 扩展 |
| `stmt_` | 语句对象的方法 |
| `_param` | 参数 |
| `_result` | 结果 |
| `_assoc` | 关联数组 |
| `_rows` | 行数 |
| `_error` / `_errno` | 错误 / 错误号 |
| `_id` | 编号 |
| `get_` | 获取 |
| `set_` | 设置 |
| `bind_` | 绑定 |
| `fetch_` | 取 |
| `free_` | 释放 |

---

## 六、常用常量

| 常量 | 含义 |
|---|---|
| `MYSQLI_ASSOC` | 关联数组（`$row['name']`） |
| `MYSQLI_NUM` | 索引数组（`$row[0]`） |
| `MYSQLI_BOTH` | 两者都有（默认） |
| `MYSQLI_REPORT_ERROR` | 出错抛异常 |
| `MYSQLI_REPORT_STRICT` | 出错抛异常（严格） |
| `MYSQLI_REPORT_OFF` | 关闭自动报错 |

---

## 七、怎么用这张表

```
每天挑 10 个：
  ① 遮住"中文"列
  ② 看函数名，自己猜
  ③ 再看答案
  ④ 记不住的标红
一周后，你的 12 个必会项应该 100% 认识。
```

**判断标准：看到 `mysqli_stmt_bind_param` 能立刻说出"给语句绑定参数"，中间不用想。**