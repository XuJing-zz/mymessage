@echo off
chcp 65001 >nul
setlocal
set "PROJ=%~dp0"
if "%PROJ:~-1%"=="\" set "PROJ=%PROJ:~0,-1%"
echo [%TIME%] 开始编译小程序到 unpackage\dist\dev\mp-weixin ...
"D:\HBuilderX\cli.exe" launch mp-weixin --project "%PROJ%" --compile true
echo [%TIME%] 编译结束，退出码 %ERRORLEVEL%（微信开发者工具会自动重载）
endlocal
