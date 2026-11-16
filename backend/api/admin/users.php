<?php
require_once __DIR__ . '/../_common.php';

api_admin();

[$page, $size, $offset] = page_params(20);

$keyword = trim($_GET['keyword'] ?? '');
$where   = '';
$params  = [];
$types   = '';

if ($keyword !== '') {
    $where    = 'WHERE username LIKE ?';
    $params[] = '%' . $keyword . '%';
    $types   .= 's';
}

$stmt = mysqli_prepare($connect, "SELECT COUNT(*) AS c FROM `user` $where");
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT id, username, role, created_at FROM `user` $where ORDER BY id ASC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, $types . 'ii', ...array_merge($params, [$size, $offset]));
mysqli_stmt_execute($stmt);

$res  = mysqli_stmt_get_result($stmt);
$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']        = (int)$row['id'];
    $row['role']      = (int)$row['role'];
    $row['role_text'] = $row['role'] === 1 ? '管理员' : '普通用户';
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);