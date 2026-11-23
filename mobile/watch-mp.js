// watch-mp.js —— 监听源码改动，自动调用 HBuilderX CLI 编译微信小程序
// 用法：双击 watch-mp.bat（或 node watch-mp.js）
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

const proj = __dirname;
const cli = 'D:\\HBuilderX\\cli.exe';
const OUT = 'unpackage\\dist\\dev\\mp-weixin';

let building = false;
let pending = false;

function build() {
  if (building) { pending = true; return; }
  building = true;
  const t = Date.now();
  const p = spawn(cli, ['launch', 'mp-weixin', '--project', proj, '--compile', 'true'], {
    cwd: proj,
    stdio: ['ignore', 'pipe', 'pipe'],
  });
  let out = '';
  p.stdout.on('data', (d) => { out += d.toString(); });
  p.stderr.on('data', (d) => { out += d.toString(); });
  p.on('error', (e) => {
    building = false;
    console.log(`[${time()}] 启动 cli.exe 失败: ${e.message}`);
  });
  p.on('close', (code) => {
    building = false;
    const s = ((Date.now() - t) / 1000).toFixed(1);
    if (code === 0) {
      console.log(`[${time()}] 编译完成 ${s}s -> ${OUT}（微信开发者工具会自动重载）`);
    } else {
      console.log(`[${time()}] 编译失败 exit=${code}`);
      const lines = out.split(/\r?\n/).filter((l) => l.trim());
      console.log(lines.slice(-12).join('\n'));
    }
    if (pending) { pending = false; setTimeout(build, 300); }
  });
}

function time() {
  return new Date().toTimeString().slice(0, 8);
}

function shouldIgnore(file) {
  const p = file.replace(/\//g, '\\');
  if (/^(unpackage|node_modules|\.hbuilderx)\\/i.test(p)) return true;
  if (p.includes('\\unpackage\\') || p.includes('\\node_modules\\') || p.includes('\\.hbuilderx\\')) return true;
  return !/\.(vue|js|json|scss|css|nvue|uts)$/i.test(p);
}

let timer = null;
function schedule() {
  if (timer) clearTimeout(timer);
  timer = setTimeout(build, 1500);
}

console.log(`监听: ${proj}`);
console.log(`产物: ${path.join(proj, OUT)}`);
console.log('保存后约 1.5 秒自动编译，Ctrl+C 退出。');

try {
  fs.watch(proj, { recursive: true }, (evt, file) => {
    if (!file || shouldIgnore(file)) return;
    schedule();
  });
} catch (e) {
  console.log('fs.watch 失败: ' + e.message);
  process.exit(1);
}

build();
