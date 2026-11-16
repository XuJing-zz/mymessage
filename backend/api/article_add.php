<?php
require_once __DIR__ . '/_common.php';

// 1. 必须登录（未登录直接 401）
$user    = api_login();
$user_id = (int)$user['id'];

// 2. 只接受 POST
need_post();

// 3. 取参数
$in      = body();
$title   = trim($in['title'] ?? '');
$content = trim($in['content'] ?? '');

// 4. 校验（用 fail()，不要用 die()，否则前端收到的不是 JSON）
if ($title === '')                          fail('标题不能为空');
if (mb_strlen($title, 'UTF-8') > 200)       fail('标题最多 200 字');
if ($content === '')                        fail('内容不能为空');
if (mb_strlen($content, 'UTF-8') < 4)       fail('内容不能少于 4 个字');

// 5. 入库（status 写死 0，不接受前端传）
$status = 0;

$stmt = mysqli_prepare($connect,
    'INSERT INTO article (user_id, title, content, status) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    fail('数据库准备失败：' . mysqli_error($connect), 500, 500);
}

mysqli_stmt_bind_param($stmt, 'issi', $user_id, $title, $content, $status);

if (!mysqli_stmt_execute($stmt)) {
    fail('写入失败：' . mysqli_stmt_error($stmt), 500, 500);
}

ok(['id' => (int)mysqli_insert_id($connect)], '提交成功，等待审核');