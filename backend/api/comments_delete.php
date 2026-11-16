<?php
require_once __DIR__ . '/_common.php';

$user = api_login();
need_post();

$in = body();
$id = (int)($in['id'] ?? 0);
if ($id <= 0) fail('缺少 id');

$stmt = mysqli_prepare($connect, 'SELECT user_id FROM comment WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) fail('评论不存在', 404, 404);

$isOwner = (int)$row['user_id'] === (int)$user['id'];
$isAdmin = (int)$user['role'] === 1;
if (!$isOwner && !$isAdmin) fail('只能删除自己的评论', 1005, 403);

$stmt = mysqli_prepare($connect, 'DELETE FROM comment WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

ok(null, '删除成功');