<?php
// 全局中间件定义文件
return [
    // Session 初始化（必须开启，否则 session() 写不进去、Cookie 也不会下发）
    \think\middleware\SessionInit::class,
];
