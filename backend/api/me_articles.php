<?php
require_once __DIR__ . '/_common.php';

$user = api_login();
[$page, $size, $offset] = page_params(10);

// 自己的文章：包含待审(0)、通过(1)、驳回(2)
$stmt = mysqli_prepare($connect, 'SELECT COUNT(*) AS c FROM article WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT a.id, a.title, a.content, a.status, a.created_at,
               (SELECT COUNT(*) FROM article_likes l  WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM comment      c2 WHERE c2.article_id = a.id) AS comment_count
        FROM article a
        WHERE a.user_id = ?
        ORDER BY a.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $user['id'], $size, $offset);
mysqli_stmt_execute($stmt);

$res  = mysqli_stmt_get_result($stmt);
$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']            = (int)$row['id'];
    $row['status']        = (int)$row['status'];
    $row['status_text']   = ['待审核', '已通过', '已驳回'][$row['status']] ?? '未知';
    $row['like_count']    = (int)$row['like_count'];
    $row['comment_count'] = (int)$row['comment_count'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);