<?php
namespace app\controller;

use think\Request;
use think\Model;

class User extends Model{} // 用户表

class Auth extends ApiBase {

    // 注册
    public function register(Request $request){
        if($err = $this->needPost($request)){ return $err; }

        $username  = trim((string)$request->post('username', ''));
        $password  = trim((string)$request->post('password', ''));
        $password2 = trim((string)$request->post('password2', ''));

        if($username === '' || $password === ''){ return $this->fail('用户名或密码不能为空'); }
        if(mb_strlen($username) > 20 ){ return $this->fail('用户名最多20个字符'); }
        if(mb_strlen($password) < 6 ){ return $this->fail('密码最少6个字符'); }
        if($password2 !== '' && $password !== $password2){ return $this->fail('两次密码不一致'); }

        // 检查用户名是否存在
        $user = User::where('username', $username)->find();
        if($user){ return $this->fail('用户名已存在'); }

        $row = User::create([
            'username' => $username,
            'password' => $password,
            'role' => 0,
        ]);

        $u = ['id'=>(int)$row->id, 'username'=>$username, 'role'=>0];
        session('user', $u);

        $token = $this->makeToken($u);
        $u['token'] = $token;

        return $this->ok($u, '注册成功');
    }

    // 登录
    public function login(Request $request){
        if($err = $this->needPost($request)){ return $err; }

        $username = trim((string)$request->post('username', ''));
        $password = trim((string)$request->post('password', ''));

        if($username === '' || $password === ''){ return $this->fail('用户名或密码不能为空'); }

        $user = User::where('username', $username)->find();
        if(!$user){ return $this->fail('用户名不存在'); }
        if($user->password !== $password){ return $this->fail('密码错误'); }

        $u = ['id'=>(int)$user->id, 'username'=>$user->username, 'role'=>(int)$user->role];
        session('user', $u);

        // 手机端靠这个 token 认人，不依赖 Cookie
        $token = $this->makeToken($u);
        $u['token'] = $token;

        return $this->ok($u, '登录成功');
    }

    // 退出
    public function logout(Request $request){
        $this->dropToken((string)$request->header('x-token', ''));
        session(null);
        return $this->ok(null,'退出成功');
    }

    // 当前用户
    public function me(Request $request){
        [$user, $err] = $this->mustLogin();
        if($err){ return $err; }

        // 不要把 token 回传给前端（它已经存过了）
        if (isset($user['token'])) unset($user['token']);

        return $this->ok($user);
    }
}
