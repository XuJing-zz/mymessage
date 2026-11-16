<?php
namespace app\controller;

use think\Request;
use think\Response;

class ApiBase
{
    protected function ok($data = null, string $msg = 'ok'): Response
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    protected function fail(string $msg = 'error', int $code = 1, int $http = 200): Response
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => null], $http);
    }

    protected function needPost(Request $request): ?Response
    {
        if (!$request->isPost()) {
            return $this->fail('请用 POST 请求', 405, 405);
        }
        return null;
    }

    protected function currentUser(): ?array
    {
        $u = session('user');
        return is_array($u) ? $u : null;
    }

    protected function mustLogin(): array
    {
        $u = $this->currentUser();
        if (!$u) return [null, $this->fail('请先登录', 401, 401)];
        return [$u, null];
    }

    protected function mustAdmin(): array
    {
        [$u, $err] = $this->mustLogin();
        if ($err) return [null, $err];
        if ((int)$u['role'] !== 1) return [null, $this->fail('需要管理员权限', 403, 403)];
        return [$u, null];
    }

    /** 分页参数：[page, size, offset] */
    protected function pageParams(Request $request, int $def = 10): array
    {
        $page = max(1, (int)$request->get('page', 1));
        $size = (int)$request->get('size', $def);
        $size = $size < 1 ? $def : min(50, $size);
        return [$page, $size, ($page - 1) * $size];
    }
}