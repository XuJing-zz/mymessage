<?php
require_once __DIR__ . '/../_common.php';

api_admin();
[$page, $size, $offset] = page_params(10);

$status  = $_GET['status'] ?? '';
$keyword = trim($_GET['keyword'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($status !== '' && in_array((int)$status, [0, 1, 2], true)) {
    $where[]  = 'a.status = ?';
    $params[] = (int)$status;
    $types   .= 'i';
}
if ($keyword !== '') {
    $where[]  = 'a.title LIKE ?';
    $params[] = '%' . $keyword . '%';
    $types   .= 's';
}
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = mysqli_prepare($connect, "SELECT COUNT(*) AS c FROM article a $w");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT a.id, a.title, a.status, a.created_at, u.username,
               (SELECT COUNT(*) FROM article_likes l  WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM comment      c2 WHERE c2.article_id = a.id) AS comment_count
        FROM article a
        JOIN `user` u ON u.id = a.user_id
        $w
        ORDER BY a.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, $types . 'ii', ...array_merge($params, [$size, $offset]));
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