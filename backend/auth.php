<?php
// 启动会话（幂等：已经启动过就不重复启动）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 返回当前登录用户，没登录返回 null
 * 数据来自 login.php 里写入的 $_SESSION['user']
 */
function current_user()
{
    return $_SESSION['user'] ?? null;
}

/**
 * 页面用：没登录就跳转到登录页
 * 注意：接口不能用它（接口要返回 JSON 401，用 _common.php 里的 api_login）
 */
function require_login(string $redirect = 'login.php')
{
    if (current_user() === null) {
        header('Location: ' . $redirect);
        exit;
    }
}