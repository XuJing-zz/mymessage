@echo off
chcp 65001 >nul
set "NODE_EXE=node"
where node >nul 2>nul
if errorlevel 1 set "NODE_EXE=D:\qianqi-env\node-v22.23.4-win-x64\node.exe"
"%NODE_EXE%" "%~dp0watch-mp.js"
pause
