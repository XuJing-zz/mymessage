<?php
namespace app\controller;

use app\model\User;
use think\Request;

class Auth extends ApiBase
{
    public function register(Request $request)
    {
        if ($err = $this->needPost($request)) return $err;

        $username = trim((string)$request->post('username', ''));
        $password = (string)$request->post('password', '');

        if ($username === '')                   return $this->fail('用户名不能为空');
        if (mb_strlen($username, 'UTF-8') < 3)  return $this->fail('用户名至少 3 个字符');
        if (mb_strlen($username, 'UTF-8') > 20) return $this->fail('用户名最多 20 个字符');
        if (mb_strlen($password, 'UTF-8') < 6)  return $this->fail('密码至少 6 位');

        if (User::findByName($username)) return $this->fail('用户名已存在', 1002);

        $id = User::createUser($username, password_hash($password, PASSWORD_DEFAULT));
        session('user', ['id' => $id, 'username' => $username, 'role' => 0]);

        return $this->ok(['id' => $id, 'username' => $username, 'role' => 0], '注册成功');
    }

    public function login(Request $request)
    {
        if ($err = $this->needPost($request)) return $err;

        $username = trim((string)$request->post('username', ''));
        $password = (string)$request->post('password', '');

        if ($username === '' || $password === '') return $this->fail('账号密码不能为空');

        $user = User::findByName($username);
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->fail('用户名或密码错误', 1001);
        }

        $u = ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => (int)$user['role']];
        session('user', $u);

        return $this->ok($u, '登录成功');
    }

    public function logout()
    {
        session(null);
        return $this->ok(null, '已退出登录');
    }

    public function me()
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        return $this->ok($user);
    }
}