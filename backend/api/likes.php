<?php
require_once __DIR__ . '/_common.php';

$user = api_login();
need_post();

$in        = body();
$articleId = (int)($in['article_id'] ?? 0);
if ($articleId <= 0) fail('缺少 article_id');

$stmt = mysqli_prepare($connect, 'SELECT id FROM article WHERE id = ? AND status = 1');
mysqli_stmt_bind_param($stmt, 'i', $articleId);
mysqli_stmt_execute($stmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
    fail('文章不存在或未审核', 404, 404);
}

$stmt = mysqli_prepare($connect, 'SELECT id FROM article_likes WHERE article_id = ? AND user_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $articleId, $user['id']);
mysqli_stmt_execute($stmt);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
    fail('你已经点过赞了', 1004);
}

$stmt = mysqli_prepare($connect, 'INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)');
mysqli_stmt_bind_param($stmt, 'ii', $articleId, $user['id']);
if (!mysqli_stmt_execute($stmt)) fail('点赞失败，请重试', 500, 500);

$stmt = mysqli_prepare($connect, 'SELECT COUNT(*) AS c FROM article_likes WHERE article_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $articleId);
mysqli_stmt_execute($stmt);
$count = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

ok(['liked' => true, 'like_count' => $count], '点赞成功');