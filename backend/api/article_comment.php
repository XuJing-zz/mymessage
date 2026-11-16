<?php
require_once __DIR__ . '/_common.php';

// 1. 取参数
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) fail('缺少 id');

$me   = current_user();
$myId = $me['id'] ?? 0;      // 没登录就是 0

// 2. 查文章（只返回已审核）
$sql = "select a.id, a.title, a.content, a.status, a.created_at,
               u.username,
               (select count(*) from article_likes l  where l.article_id = a.id) as like_count,
               (select count(*) from article_likes l2 where l2.article_id = a.id and l2.user_id = ?) as liked
        from article a
        join `user` u on u.id = a.user_id
        where a.id = ? and a.status = 1";

$stmt = mysqli_prepare($connect, $sql);
if (!$stmt) fail('SQL 准备失败：' . mysqli_error($connect), 500, 500);

mysqli_stmt_bind_param($stmt, 'ii', $myId, $id);
mysqli_stmt_execute($stmt);
$article = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$article) fail('文章不存在或未审核', 404, 404);

// 3. 查评论
$stmt = mysqli_prepare($connect,
    'select c.id, c.content, c.created_at, u.username
     from comment c
     join `user` u on u.id = c.user_id
     where c.article_id = ?
     order by c.id desc');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

$comments = [];
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $row['id'] = (int)$row['id'];
    $comments[] = $row;
}

// 4. 类型转换
$article['id']         = (int)$article['id'];
$article['status']     = (int)$article['status'];
$article['like_count'] = (int)$article['like_count'];
$article['liked']      = (bool)$article['liked'];

// 5. 一起返回
ok([
    'article'  => $article,
    'comments' => $comments,
]);