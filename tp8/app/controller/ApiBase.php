<?php
namespace app\controller;

use  think\Request;
use  think\Response;
use  think\Model;

class ApiBase extends Model
{
    // 检查请求是否为POST方法
    protected function needPost(Request $request){
        if($request->isPost()){ return $this->fail('请使用POST方法',405,405); }
        return null;
    }

    protected function currentUser(){
        $u = session('user');
        return is_array($u) ? $u : null;
    }   

    // 检查用户是否登录
    protected function mustLogin(Request $request){
        $u = is_array(session('user')) ? session('user') : null;
        if(!$u){ return $this->fail('请先登录'); }
        return [$u, null];
    }

    //分页
    protected function pageParams(Request $request, int $def = 10){   // 默认每页10条
        $page = max(1, (int)$request->get('page', 1));          // 当前页码
        $size = (int)$request->get('size', $def);             // 每页条数
        $size = $size < 1 ? $def : min(50, $size);            // 最大50条
        return [$page, $size, ($page-1)*$size];                 // 返回当前页码、每页条数、偏移量
    }
}
