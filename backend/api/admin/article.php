<?php
require_once __DIR__ . '/../_common.php';

api_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) fail('缺少 id');

$sql = "SELECT a.id, a.title, a.content, a.status, a.created_at, u.username,
               (SELECT COUNT(*) FROM article_likes l  WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM comment      c2 WHERE c2.article_id = a.id) AS comment_count
        FROM article a
        JOIN `user` u ON u.id = a.user_id
        WHERE a.id = ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$a = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$a) fail('文章不存在', 404, 404);

$a['id']            = (int)$a['id'];
$a['status']        = (int)$a['status'];
$a['status_text']   = ['待审核', '已通过', '已驳回'][$a['status']] ?? '未知';
$a['like_count']    = (int)$a['like_count'];
$a['comment_count'] = (int)$a['comment_count'];

// 附带评论，管理员详情页要能删评论
$stmt = mysqli_prepare($connect,
    'SELECT c.id, c.content, c.created_at, u.username
     FROM comment c JOIN `user` u ON u.id = c.user_id
     WHERE c.article_id = ? ORDER BY c.id DESC');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

$comments = [];
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $row['id'] = (int)$row['id'];
    $comments[] = $row;
}

ok(['article' => $a, 'comments' => $comments]);