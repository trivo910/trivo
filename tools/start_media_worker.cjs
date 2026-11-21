// Start the local image-only worker without changing system PHP configuration.
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync, spawn, spawnSync } = require('node:child_process');

const root = path.resolve(__dirname, '..');
const directory = path.join(root, '.audit', 'media-worker');
const statePath = path.join(directory, 'worker.json');
fs.mkdirSync(directory, { recursive: true });
const php = execFileSync('php', ['-r', 'echo PHP_BINARY;'], { encoding: 'utf8', windowsHide: true }).trim();

function startTime(pid) {
    if (!Number.isInteger(pid) || pid < 1) return null;
    try {
        process.kill(pid, 0);
        if (process.platform !== 'win32') return null;
        return execFileSync('powershell.exe', ['-NoProfile', '-Command',
            `(Get-Process -Id ${pid} -ErrorAction Stop).StartTime.ToUniversalTime().Ticks`],
        { encoding: 'utf8', windowsHide: true, stdio: ['ignore', 'pipe', 'ignore'] }).trim();
    } catch { return null; }
}

(async () => {
    if (fs.existsSync(statePath)) {
        const state = JSON.parse(fs.readFileSync(statePath, 'utf8'));
        if (state.php === php && state.started && startTime(state.pid) === state.started) {
            console.log(`Image worker is already running (PID ${state.pid}).`);
            return;
        }
    }
    const args = [];
    const gd = spawnSync(php, ['-r', "exit(extension_loaded('gd') ? 0 : 1);"], { windowsHide: true });
    if (gd.status !== 0) args.push('-d', 'extension=gd');
    args.push('artisan', 'queue:work', 'database', '--queue=media', '--sleep=1', '--tries=1', '--timeout=60');
    const output = fs.openSync(path.join(directory, 'output.log'), 'a');
    const errors = fs.openSync(path.join(directory, 'error.log'), 'a');
    const worker = spawn(php, args, { cwd: root, detached: true, windowsHide: true, stdio: ['ignore', output, errors] });
    try {
        await new Promise((resolve, reject) => {
            worker.once('error', reject);
            setTimeout(resolve, 1000);
        });
        if (worker.exitCode !== null) throw new Error('Image worker stopped. Check .audit/media-worker/error.log.');
        fs.writeFileSync(statePath, JSON.stringify({ pid: worker.pid, php, started: startTime(worker.pid) }, null, 2));
        worker.unref();
        console.log(`Image-only worker started (PID ${worker.pid}). Logs: .audit/media-worker/`);
    } finally {
        fs.closeSync(output);
        fs.closeSync(errors);
    }
})().catch(error => { console.error(error.message); process.exitCode = 1; });
