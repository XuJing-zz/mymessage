<?php
    include_once __DIR__.'/../db.php';
    include_once __DIR__.'/../auth.php';

    header('Content-Type: application/json; charset=utf-8');    //header 设置响应头为 JSON 格式，编码为 UTF-8, Content-Type是响应体的类型，application/json 是 JSON 格式的响应体，charset=utf-8 是响应体的编码为 UTF-8

    // 跨域：带 Cookie 时不能用 *，必须回显具体 Origin
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';    //HTTP_ORIGIN 是请求头中的一个字段，用于指定请求的来源，即请求的域名
    $allow  = ['http://localhost', 'http://localhost:5173', 'http://127.0.0.1:5173'];
    if (in_array($origin, $allow, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    } else {
        header('Access-Control-Allow-Origin: *');   // 无凭证的公开接口
    }
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
    
    /** 成功 */
    function ok($data = null, $msg = 'ok') {
        echo json_encode(['code' => 0, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /** 失败 */
    function fail($msg = 'error', $code = 1, $http = 200) {
        http_response_code($http);
        echo json_encode(['code' => $code, 'msg' => $msg, 'data' => null], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /** 读请求体：JSON 或表单 */
    function body() {
        $raw = file_get_contents('php://input');
        if ($raw !== '' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $j = json_decode($raw, true);
            return is_array($j) ? $j : [];
        }
        return $_POST;
    }
    
    /** 只允许 POST */
    function need_post() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            fail('请用 POST 请求', 405, 405);
        }
    }
    
    /** 必须登录 */
    function api_login() {
        $u = current_user();
        if (!$u) fail('请先登录', 401, 401);
        return $u;
    }
    
    /** 必须是管理员 */
    function api_admin() {
        $u = api_login();
        if ((int)($u['role'] ?? 0) !== 1) {
            fail('需要管理员权限', 403, 403);
        }
        return $u;
    }
    
    /** 分页参数 [page, size, offset] */
    function page_params($default = 10) {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $size = (int)($_GET['size'] ?? $default);
        $size = $size < 1 ? $default : min(50, $size);
        return [$page, $size, ($page - 1) * $size];
    }