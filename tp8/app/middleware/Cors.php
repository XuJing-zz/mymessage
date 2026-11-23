<?php
declare(strict_types=1);

namespace app\middleware;

use think\middleware\AllowCrossDomain;

/**
 * 在框架默认跨域中间件基础上，补上 X-Token 允许头。
 * 小程序/H5 把登录 token 放在 X-Token 里，浏览器预检（OPTIONS）必须显式放行，
 * 否则请求会被浏览器拦掉，前端看到的就是“连接服务器超时”。
 */
class Cors extends AllowCrossDomain
{
    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, X-Token, X-CSRF-TOKEN, X-Requested-With, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since',
    ];
}
