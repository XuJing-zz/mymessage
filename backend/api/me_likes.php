<?php
require_once __DIR__ . '/_common.php';

$user = api_login();
[$page, $size, $offset] = page_params(20);

$stmt = mysqli_prepare($connect,
    'SELECT COUNT(*) AS c
     FROM article_likes l JOIN article a ON a.id = l.article_id
     WHERE l.user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT a.id, a.title, a.created_at, u.username,
               (SELECT COUNT(*) FROM article_likes l2 WHERE l2.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM comment      c2 WHERE c2.article_id = a.id) AS comment_count
        FROM article_likes l
        JOIN article a ON a.id = l.article_id
        JOIN `user`  u ON u.id = a.user_id
        WHERE l.user_id = ?
        ORDER BY l.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $user['id'], $size, $offset);
mysqli_stmt_execute($stmt);

$res  = mysqli_stmt_get_result($stmt);
$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']            = (int)$row['id'];
    $row['like_count']    = (int)$row['like_count'];
    $row['comment_count'] = (int)$row['comment_count'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);