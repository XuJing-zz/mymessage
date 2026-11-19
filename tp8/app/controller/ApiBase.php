<?php
namespace app\controller;

use think\Request;
use think\Response;

class ApiBase
{
    protected function ok($data = null, string $msg = 'ok')
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    protected function fail(string $msg = 'error', int $code = 1, int $http = 200)
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => null], $http);
    }

    protected function needPost(Request $request)
    {
        if (!$request->isPost()) {
            return $this->fail('请用 POST 请求', 405, 405);
        }
        return null;
    }

    /**
     * 当前登录用户：先看 Session（浏览器后台），再看 X-Token（手机端/小程序）
     */
    protected function currentUser()
    {
        $u = session('user');
        if (is_array($u)) return $u;

        $token = (string)request()->header('x-token', '');
        if ($token === '') return null;

        $cached = \think\facade\Cache::get('utoken:' . $token);
        return is_array($cached) ? $cached : null;
    }

    protected function mustLogin()
    {
        $u = $this->currentUser();
        if (!$u) {
            return [null, $this->fail('请先登录', 401, 401)];
        }
        return [$u, null];
    }

    protected function mustAdmin()
    {
        [$u, $err] = $this->mustLogin();
        if ($err) return [null, $err];
        if ((int)$u['role'] !== 1) return [null, $this->fail('需要管理员权限', 403, 403)];
        return [$u, null];
    }

    /** 生成登录令牌，有效期 7 天 */
    protected function makeToken(array $user): string
    {
        $token = md5($user['id'] . '|' . microtime(true) . '|' . random_int(1000, 9999));
        \think\facade\Cache::set('utoken:' . $token, $user, 86400 * 7);
        return $token;
    }

    /** 注销令牌 */
    protected function dropToken(string $token): void
    {
        if ($token !== '') {
            \think\facade\Cache::delete('utoken:' . $token);
        }
    }

    /** 分页参数：[page, size, offset] */
    protected function pageParams(Request $request, int $def = 5)
    {
        $page = max(1, (int)$request->get('page', 1));
        $size = (int)$request->get('size', $def);
        $size = $size < 1 ? $def : min(50, $size);
        return [$page, $size, ($page - 1) * $size];
    }
}
