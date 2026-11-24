<?php
use think\facade\Route;

// 认证
Route::post('api/register', 'Auth/register');
Route::post('api/login',    'Auth/login');
Route::post('api/logout',   'Auth/logout');
Route::get('api/me',        'Auth/me');

// 文章
Route::get('api/articles/counts',  'Article/counts');
Route::get('api/articles',        'Article/index');
Route::get('api/article',         'Article/read');
Route::post('api/article',        'Article/add');
Route::post('api/article/delete', 'Article/remove');

// 健康检查
Route::get('/', function () {
    return json(['code' => 0, 'msg' => 'ThinkPHP API 已启动', 'data' => null]);
});
// 评论
Route::get('api/comments',         'Comment/index');
Route::post('api/comment',         'Comment/add');
Route::post('api/comment/delete',  'Comment/remove');

// 点赞
Route::post('api/like',    'Like/like');
Route::post('api/unlike',  'Like/unlike');

// 我的
Route::get('api/me/articles', 'Me/articles');
Route::get('api/me/comments', 'Me/comments');
Route::get('api/me/likes',    'Me/likes');

// 管理端
Route::get('api/admin/articles',        'Admin/articles');
Route::get('api/admin/article',         'Admin/article');
Route::post('api/admin/article/audit',  'Admin/audit');
Route::post('api/admin/article/update', 'Admin/update');
Route::post('api/admin/comment/delete', 'Admin/commentDelete');
Route::get('api/admin/comments',        'Admin/comments');
Route::get('api/admin/users',           'Admin/users');

// 兜底：没匹配到任何路由时，也返回 JSON（而不是 HTML 报错页）
// 常见原因：用 GET 打开了只允许 POST 的接口
Route::miss(function () {
    $method = request()->method();
    return json([
        'code' => 404,
        'msg'  => '接口不存在或请求方法不对（当前 ' . $method . '）',
        'data' => null,
    ], 404);
});
