<?php
require_once __DIR__ . '/_common.php';

$articleId = (int)($_GET['article_id'] ?? 0);
if ($articleId <= 0) fail('缺少 article_id');

$stmt = mysqli_prepare($connect,
    'SELECT c.id, c.user_id, c.content, c.created_at, u.username
     FROM comment c
     JOIN `user` u ON u.id = c.user_id
     WHERE c.article_id = ?
     ORDER BY c.id DESC');
mysqli_stmt_bind_param($stmt, 'i', $articleId);
mysqli_stmt_execute($stmt);

$res  = mysqli_stmt_get_result($stmt);
$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']      = (int)$row['id'];
    $row['user_id'] = (int)$row['user_id'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => count($list)]);