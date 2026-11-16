<?php
require_once __DIR__ . '/../_common.php';

api_admin();
need_post();

$in     = body();
$id     = (int)($in['id'] ?? 0);
$status = (int)($in['status'] ?? -1);

if ($id <= 0)                         fail('缺少 id');
if (!in_array($status, [1, 2], true)) fail('status 只能是 1（通过）或 2（驳回）');

$stmt = mysqli_prepare($connect, 'UPDATE article SET status = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $status, $id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 0) {
    $chk = mysqli_prepare($connect, 'SELECT id FROM article WHERE id = ?');
    mysqli_stmt_bind_param($chk, 'i', $id);
    mysqli_stmt_execute($chk);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) fail('文章不存在', 404, 404);
}

ok(null, $status === 1 ? '审核通过' : '已驳回');