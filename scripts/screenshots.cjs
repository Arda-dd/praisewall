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
  await shot('embed', '/walls/orbit/embed', { height: 1100 });
  await shot('walls', '/walls', { height: 480 });

  await browser.close();
})();
