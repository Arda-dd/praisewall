// Takes the README screenshots from a running instance with the demo data:
//   php artisan db:seed --class=DemoSeeder && php artisan serve
//   node scripts/screenshots.cjs http://127.0.0.1:8000
// Needs puppeteer, which isn't a dependency: npx -p puppeteer node scripts/screenshots.cjs ...
const puppeteer = require(process.env.PUPPETEER_PATH || 'puppeteer');
const path = require('path');

const base = process.argv[2] || 'http://127.0.0.1:8000';
const out = path.join(__dirname, '..', 'docs');
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

(async () => {
  const browser = await puppeteer.launch({ headless: true, args: ['--lang=en-US'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1280, height: 820, deviceScaleFactor: 2 });

  const shot = async (name, url, { dark = false, full = false, height } = {}) => {
    await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: dark ? 'dark' : 'light' }]);
    await page.goto(base + url, { waitUntil: 'networkidle0' });
    await wait(500);
    const clip = height ? { x: 0, y: 0, width: 1280, height } : undefined;
    await page.screenshot({ path: path.join(out, name + '.png'), fullPage: full && !clip, clip });
  };

  await shot('wall', '/w/orbit', { height: 1000 });
  await shot('wall-dark', '/w/orbit', { dark: true, height: 1000 });
  await shot('collect', '/c/orbit', { full: true });

  await page.goto(base + '/login', { waitUntil: 'networkidle0' });
  await page.type('#email', 'demo@example.com');
  await page.type('#password', 'password');
  await Promise.all([page.waitForNavigation(), page.click('button')]);

  await shot('inbox', '/walls/orbit', { height: 900 });
  await shot('walls', '/walls', { height: 480 });

  // The widget designer, with a vivid background so the Glass style shows.
  await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: 'light' }]);
  await page.goto(base + '/walls/orbit/embed', { waitUntil: 'networkidle0' });
  await page.click('input[name="layout"][value="grid"] + span');
  await page.click('input[name="style"][value="glass"] + span');
  await page.select('select[name="limit"]', '6');
  await page.evaluate(() => document.querySelectorAll('[data-preview-bg]')[3].click());
  await wait(800);
  await page.screenshot({ path: path.join(out, 'embed.png'), clip: { x: 0, y: 0, width: 1280, height: 1150 } });

  // A testimonial as a social image, straight from the canvas.
  await page.goto(base + '/walls/orbit?status=approved', { waitUntil: 'networkidle0' });
  await page.goto(await page.$eval('a[href*="/image"]', (a) => a.href), { waitUntil: 'networkidle0' });
  await wait(600);
  const png = await page.$eval('[data-canvas]', (canvas) => canvas.toDataURL('image/png'));
  require('fs').writeFileSync(path.join(out, 'share.png'), Buffer.from(png.split(',')[1], 'base64'));

  // All six styles side by side.
  const styles = ['classic', 'minimal', 'bubble', 'glass', 'bold', 'elegant'];
  await page.setContent(`<!doctype html><html><body style="margin:0;padding:36px;font-family:system-ui;background:#f4f4f5">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:28px">
      ${styles.map((style) => `<div style="padding:22px;border-radius:20px;background:${style === 'glass' ? 'radial-gradient(at 15% 20%,#a78bfa,transparent 55%),radial-gradient(at 85% 25%,#f9a8d4,transparent 55%),radial-gradient(at 50% 95%,#67e8f9,transparent 55%),#312e81' : '#fff'}">
        <p style="margin:0 0 14px;font-weight:700;font-size:15px;color:${style === 'glass' ? '#fff' : '#18181b'};text-transform:capitalize">${style}</p>
        <div data-praisewall="${base}/api/walls/orbit" data-layout="list" data-style="${style}" data-theme="light" data-limit="1" data-cta="false" data-branding="false"></div>
      </div>`).join('')}
    </div>
    <script src="${base}/widget.js"></script></body></html>`, { waitUntil: 'load' });
  await page.waitForFunction(() => [...document.querySelectorAll('[data-praisewall]')].every((el) => el.shadowRoot?.querySelector('.card')), { timeout: 20000 });
  await wait(500);
  const height = await page.evaluate(() => document.body.scrollHeight);
  await page.screenshot({ path: path.join(out, 'styles.png'), clip: { x: 0, y: 0, width: 1280, height } });

  await browser.close();
})();
