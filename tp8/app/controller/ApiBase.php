<?php
namespace app\controller;

use think\Request;
use think\Response;

class ApiBase
{
    /** 统一成功 */
    protected function ok($data = null, string $msg = 'ok'): Response
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    /** 统一失败 */
    protected function fail(string $msg = 'error', int $code = 1, int $http = 200): Response
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => null], $http);
    }

    /** 只允许 POST */
    protected function needPost(Request $request): ?Response
    {
        if (!$request->isPost()) {
            return $this->fail('请用 POST 请求', 405, 405);
        }
        return null;
    }

    /** 当前登录用户（没有则 null） */
    protected function currentUser(): ?array
    {
        $u = session('user');
        return is_array($u) ? $u : null;
    }

    /** 必须登录：返回 [user, null] 或 [null, response] */
    protected function mustLogin(): array
    {
        $u = $this->currentUser();
        if (!$u) {
            return [null, $this->fail('请先登录', 401, 401)];
        }
        return [$u, null];
    }

    /** 必须是管理员 */
    protected function mustAdmin(): array
    {
        [$u, $err] = $this->mustLogin();
        if ($err) return [null, $err];
        if ((int)$u['role'] !== 1) {
            return [null, $this->fail('需要管理员权限', 403, 403)];
        }
        return [$u, null];
    }
}