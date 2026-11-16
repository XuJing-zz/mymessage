<?php
require_once __DIR__ . '/_common.php';
need_post();

$in       = body();
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($username === '' || $password === '') fail('账号密码不能为空');

$stmt = mysqli_prepare($connect, 'SELECT id, username, password, role FROM `user` WHERE username = ?');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$u || !password_verify($password, $u['password'])) {
    fail('用户名或密码错误', 1001);
}

$_SESSION['user'] = [
    'id'       => (int)$u['id'],
    'username' => $u['username'],
    'role'     => (int)$u['role'],
];

ok(['id' => (int)$u['id'], 'username' => $u['username'], 'role' => (int)$u['role']], '登录成功');