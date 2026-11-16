<?php
require_once __DIR__ . '/_common.php';
need_post();

$in       = body();
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($username === '')                   fail('用户名不能为空');
if (mb_strlen($username, 'UTF-8') < 3)  fail('用户名至少 3 个字符');
if (mb_strlen($username, 'UTF-8') > 20) fail('用户名最多 20 个字符');
if (mb_strlen($password, 'UTF-8') < 6)  fail('密码至少 6 位');

$stmt = mysqli_prepare($connect, 'SELECT id FROM `user` WHERE username = ?');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
    fail('用户名已存在', 1002);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 0;

$stmt = mysqli_prepare($connect, 'INSERT INTO `user` (username, password, role) VALUES (?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'ssi', $username, $hash, $role);
if (!mysqli_stmt_execute($stmt)) fail('注册失败，请重试', 500, 500);

$id = (int)mysqli_insert_id($connect);
$_SESSION['user'] = ['id' => $id, 'username' => $username, 'role' => 0];

ok(['id' => $id, 'username' => $username, 'role' => 0], '注册成功');