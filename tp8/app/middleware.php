<?php
// 全局中间件定义文件
return [
    // 跨域（手机端 H5 直连后端时需要）
    \think\middleware\AllowCrossDomain::class,
    // Session 初始化（必须开启，否则 session() 写不进去、Cookie 也不会下发）
    \think\middleware\SessionInit::class,
];