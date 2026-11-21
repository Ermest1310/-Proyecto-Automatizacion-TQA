const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { chromium } = require('@playwright/test');
const { Manager } = require('../../backend/manager.cjs');
const { start } = require('../../backend/server.cjs');
const { PlatformPage } = require('../../pages/platform/PlatformPage.cjs');

test('POM de plataforma guarda metadatos, selecciona casos y limpia la sesión en navegador', { timeout: 60000 }, async () => {
  const root = fs.mkdtempSync(path.join(os.tmpdir(), 'qa-pom-'));
  fs.mkdirSync(path.join(root, 'config'));
  fs.copyFileSync(path.join(__dirname, '../../config/test-catalog.json'), path.join(root, 'config/test-catalog.json'));
  const manager = new Manager({ root });
  const token = 'isolated-pom-test-token-at-least-32-characters';
  const { server } = start({ manager, port: 0, token, lock: false });
  let browser;
  try {
    await new Promise(resolve => server.once('listening', resolve));
    const base = 'http://127.0.0.1:' + server.address().port;
    browser = await chromium.launch(process.env.QA_POM_BROWSER_CHANNEL ? { channel: process.env.QA_POM_BROWSER_CHANNEL } : {});
    const context = await browser.newContext({ viewport: { width: 1440, height: 1100 } });
    const page = await context.newPage();
    const platform = new PlatformPage(page, base);
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await assert.rejects(PlatformPage.authenticate(context, base, 'invalid'), /sesión/);
    await PlatformPage.authenticate(context, base, token);
    await platform.open();
    await platform.caseTable.waitFor({ state: 'attached' });
    await platform.openCatalog();
    await platform.newCase();
    await platform.editor.identifier.fill('QA-POM-CASE');
    await platform.editor.name.fill('Caso aislado de POM');
    await platform.editor.reason.fill('Verificar persistencia real del editor');
    await platform.editor.save();
    const saved = await (await context.request.get(base + '/api/platform/entities/cases/QA-POM-CASE')).json();
    assert.equal(saved.data.name, 'Caso aislado de POM');
    await platform.catalogSearch.fill('QA-POM-CASE');
    await platform.caseTable.getByText('Pendiente', { exact: true }).waitFor();

    await platform.openSuites();
    await platform.suites.create();
    await platform.editor.identifier.fill('SUITE-POM');
    await platform.editor.name.fill('Suite aislada de POM');
    await platform.editor.category.selectOption('demonstration');
    await platform.editor.chooseCase('QA-DEMO-PASS');
    assert.equal(await platform.editor.caseIds.inputValue(), 'QA-DEMO-PASS');
    await platform.editor.reason.fill('Verificar selección real y vista previa');
    await platform.editor.save();
    await platform.suites.preview('SUITE-POM');
    await platform.suites.root.getByText(/Plan ejecutable/).waitFor();
    const plan = await (await context.request.get(base + '/api/platform/suites/SUITE-POM/plan')).json();
    assert.deepEqual(plan.caseIds, ['QA-DEMO-PASS']);
    assert.equal(plan.runnable, true);
    // Exercise response synchronization without starting a runner or sending mail.
    await page.route(base + '/api/platform/suites/SUITE-POM/execute', route => route.fulfill({
      status: 422, contentType: 'application/json', body: JSON.stringify({ error: 'POM controlled rejection' }),
    }));
    await platform.suites.recipient.fill('pom@localhost.test');
    assert.equal((await platform.suites.execute('SUITE-POM')).status(), 422);
    await platform.useMobileViewport();
    assert.equal(await platform.fitsViewport(), true);
    await platform.logout();
    assert.equal(await platform.caseTable.count(), 0);
    assert.equal((await context.request.get(base + '/api/catalog')).status(), 401);
    assert.deepEqual(errors, []);
  } finally {
    if (browser) await browser.close();
    await manager.stop();
    await new Promise(resolve => server.close(resolve));
    // Only the temporary root created by this test is removed.
    fs.rmSync(root, { recursive: true, force: true });
  }
});
