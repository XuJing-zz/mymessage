<?php
require_once __DIR__ . '/_common.php';

$user = api_login();
[$page, $size, $offset] = page_params(20);

$stmt = mysqli_prepare($connect, 'SELECT COUNT(*) AS c FROM comment WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT c.id, c.article_id, c.content, c.created_at,
               a.title AS article_title
        FROM comment c
        JOIN article a ON a.id = c.article_id
        WHERE c.user_id = ?
        ORDER BY c.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $user['id'], $size, $offset);
mysqli_stmt_execute($stmt);

$res  = mysqli_stmt_get_result($stmt);
$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']         = (int)$row['id'];
    $row['article_id'] = (int)$row['article_id'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);