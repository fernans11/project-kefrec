import { spawn } from 'node:child_process';
import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const baseUrl = 'http://127.0.0.1:8000';
const outputDir = path.resolve('docs/screenshots-aktual');
const profilesDir = path.resolve('storage/app/screenshot-profiles');

const scenarios = [
  { name: 'guest', page: '/', expected: '/' },
  { name: 'member', login: '/login', email: 'member01@kefrec.test', password: 'Member123!', page: '/home', expected: '/home' },
  { name: 'kasir', login: '/login', email: 'kasir.demo@kefrec.test', password: 'DemoKefrec123!', page: '/cashier/orders', expected: '/cashier/orders' },
  { name: 'dapur', login: '/login', email: 'dapur.demo@kefrec.test', password: 'DemoKefrec123!', page: '/kitchen/orders', expected: '/kitchen/orders' },
  { name: 'admin-filament', login: '/admin/login', email: 'admin.demo@kefrec.test', password: 'DemoKefrec123!', page: '/admin', expected: '/admin', height: 1800 },
  { name: 'owner-filament', login: '/admin/login', email: 'owner.demo@kefrec.test', password: 'DemoKefrec123!', page: '/admin', expected: '/admin', height: 1800 },
];

await mkdir(outputDir, { recursive: true });
await mkdir(profilesDir, { recursive: true });

function wait(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function waitForJson(url, attempts = 50) {
  for (let attempt = 0; attempt < attempts; attempt++) {
    try {
      const response = await fetch(url);
      if (response.ok) return await response.json();
    } catch {}
    await wait(200);
  }
  throw new Error(`Chrome DevTools tidak tersedia pada ${url}`);
}

async function captureScenario(scenario, index) {
  const port = 9300 + index;
  const profile = path.join(profilesDir, scenario.name);
  const chrome = spawn(chromePath, [
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-first-run',
    '--no-default-browser-check',
    `--remote-debugging-port=${port}`,
    `--user-data-dir=${profile}`,
    '--window-size=1440,1200',
    'about:blank',
  ], { stdio: 'ignore' });

  try {
    const targets = await waitForJson(`http://127.0.0.1:${port}/json`);
    const target = targets.find(item => item.type === 'page');
    if (!target) throw new Error('Target halaman Chrome tidak ditemukan.');

    const socket = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve, reject) => {
      socket.addEventListener('open', resolve, { once: true });
      socket.addEventListener('error', reject, { once: true });
    });

    let commandId = 0;
    const pending = new Map();
    socket.addEventListener('message', event => {
      const message = JSON.parse(event.data);
      if (!message.id || !pending.has(message.id)) return;
      const { resolve, reject } = pending.get(message.id);
      pending.delete(message.id);
      if (message.error) reject(new Error(message.error.message));
      else resolve(message.result);
    });

    const send = (method, params = {}) => new Promise((resolve, reject) => {
      const id = ++commandId;
      pending.set(id, { resolve, reject });
      socket.send(JSON.stringify({ id, method, params }));
    });

    await send('Page.enable');
    await send('Runtime.enable');
    await send('Emulation.setDeviceMetricsOverride', {
      width: 1440, height: scenario.height || 1200, deviceScaleFactor: 1, mobile: false,
    });

    if (scenario.login) {
      await send('Page.navigate', { url: baseUrl + scenario.login });
      await wait(2500);
      const expression = `(() => {
        const email = document.querySelector('input[type="email"], input[name="email"], input[autocomplete="username"]');
        const password = document.querySelector('input[type="password"]');
        if (!email || !password) return { ok: false, title: document.title, url: location.href };
        const setValue = (element, value) => {
          const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
          setter.call(element, value);
          element.dispatchEvent(new Event('input', { bubbles: true }));
          element.dispatchEvent(new Event('change', { bubbles: true }));
        };
        setValue(email, ${JSON.stringify(scenario.email)});
        setValue(password, ${JSON.stringify(scenario.password)});
        const form = email.closest('form') || password.closest('form');
        if (!form) return { ok: false, title: document.title, url: location.href };
        form.requestSubmit();
        return { ok: true };
      })()`;
      const loginResult = await send('Runtime.evaluate', { expression, returnByValue: true });
      if (!loginResult.result.value?.ok) {
        throw new Error(`Form login tidak ditemukan untuk ${scenario.name}.`);
      }
      await wait(4000);
    }

    await send('Page.navigate', { url: baseUrl + scenario.page });
    await wait(scenario.login?.startsWith('/admin') ? 7000 : 4000);
    const pageState = await send('Runtime.evaluate', {
      expression: `({ url: location.pathname, title: document.title, text: document.body.innerText.slice(0, 300) })`,
      returnByValue: true,
    });
    const state = pageState.result.value;
    if (!state.url.startsWith(scenario.expected)) {
      throw new Error(`${scenario.name} diarahkan ke ${state.url}, bukan ${scenario.expected}.`);
    }

    const screenshot = await send('Page.captureScreenshot', {
      format: 'png', captureBeyondViewport: false, fromSurface: true,
    });
    await writeFile(path.join(outputDir, `${scenario.name}.png`), Buffer.from(screenshot.data, 'base64'));
    socket.close();
    return state;
  } finally {
    chrome.kill();
  }
}

const requestedScenario = process.argv[2];
const selectedScenarios = requestedScenario
  ? scenarios.filter(scenario => scenario.name === requestedScenario)
  : scenarios;

for (let index = 0; index < selectedScenarios.length; index++) {
  const scenario = selectedScenarios[index];
  const state = await captureScenario(scenario, index);
  process.stdout.write(`${scenario.name}: ${state.url} | ${state.title}\n`);
}
