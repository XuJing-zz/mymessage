<?php
require_once __DIR__ . '/_common.php';

$user = api_login();
need_post();

$in        = body();
$articleId = (int)($in['article_id'] ?? 0);
$content   = trim($in['content'] ?? '');

if ($articleId <= 0)                    fail('缺少 article_id');
if ($content === '')                    fail('评论不能为空');
if (mb_strlen($content, 'UTF-8') > 500) fail('评论最多 500 字');

$stmt = mysqli_prepare($connect, 'SELECT id FROM article WHERE id = ? AND status = 1');
mysqli_stmt_bind_param($stmt, 'i', $articleId);
mysqli_stmt_execute($stmt);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
    fail('文章不存在或未审核', 404, 404);
}

$stmt = mysqli_prepare($connect, 'INSERT INTO comment (article_id, user_id, content) VALUES (?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'iis', $articleId, $user['id'], $content);
if (!mysqli_stmt_execute($stmt)) fail('评论失败，请重试', 500, 500);

ok(['id' => (int)mysqli_insert_id($connect)], '评论成功');