@echo off
chcp 65001 >nul
set PHP=D:\phpstudy_pro\Extensions\php\php8.0.2nts
set NGX=D:\phpstudy_pro\Extensions\Nginx1.25.2
set MYSQL=D:\phpstudy_pro\Extensions\MySQL5.7.26
set PROJ=D:\phpstudy_pro\WWW\message_uniApp
set NODE=D:\qianqi-env\node-v22.23.4-win-x64\node.exe
set VITECWD=D:\HBuilderX\plugins\uniapp-cli-vite

echo [1/4] MySQL 3306
netstat -ano | findstr ":3306 " | findstr LISTENING >nul || start "" /min "%MYSQL%\bin\mysqld.exe" --defaults-file="%MYSQL%\my.ini"
timeout /t 2 >nul

echo [2/4] php-cgi 9000
netstat -ano | findstr ":9000 " | findstr LISTENING >nul || start "" /min "%PHP%\php-cgi.exe" -b 127.0.0.1:9000 -c "%PHP%\php.ini"
timeout /t 2 >nul

echo [3/4] nginx 80
netstat -ano | findstr ":80 " | findstr LISTENING >nul || start "" /min "%NGX%\nginx.exe" -p "%NGX%" -c conf\nginx.conf
timeout /t 2 >nul

echo [4/4] H5 dev server 5173 (hot reload)
start "H5 dev 5173" cmd /k "cd /d %VITECWD% && set UNI_PLATFORM=h5&& set UNI_INPUT_DIR=%PROJ%&& set UNI_OUTPUT_DIR=%PROJ%\unpackage\dist\dev\h5&& set UNI_HBUILDERX_PLUGINS=D:\HBuilderX\plugins&& "%NODE%" node_modules\vite\bin\vite.js --config vite.config.js --port 5173 --host 127.0.0.1"

echo.
echo Done. Open http://localhost:5173/  (keep this window and the dev window open)
echo Edit .vue files in the project - the browser updates automatically.
timeout /t 3 >nul
