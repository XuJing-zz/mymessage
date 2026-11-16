<?php
require_once __DIR__ . '/../_common.php';

api_admin();

[$page, $size, $offset] = page_params(20);

$articleId = (int)($_GET['article_id'] ?? 0);
$keyword   = trim($_GET['keyword'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($articleId > 0) {
    $where[]  = 'c.article_id = ?';
    $params[] = $articleId;
    $types   .= 'i';
}
if ($keyword !== '') {
    $where[]  = '(c.content LIKE ? OR u.username LIKE ? OR a.title LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw; $params[] = $kw; $params[] = $kw;
    $types   .= 'sss';
}
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = mysqli_prepare($connect, "SELECT COUNT(*) AS c FROM comment c
    JOIN `user` u ON u.id = c.user_id
    JOIN article a ON a.id = c.article_id
    $w");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT c.id, c.article_id, c.user_id, c.content, c.created_at,
               u.username, a.title AS article_title
        FROM comment c
        JOIN `user` u ON u.id = c.user_id
        JOIN article a ON a.id = c.article_id
        $w
        ORDER BY c.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, $types . 'ii', ...array_merge($params, [$size, $offset]));
mysqli_stmt_execute($stmt);

$res  = mysqli_stmt_get_result($stmt);
$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']         = (int)$row['id'];
    $row['article_id'] = (int)$row['article_id'];
    $row['user_id']    = (int)$row['user_id'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);