const assert = require('node:assert/strict');
const { MetadataEditor } = require('../../components/platform/MetadataEditor.cjs');
const { SuitesPage } = require('./SuitesPage.cjs');

/** Page object for the QA platform shell (CommonJS validation scripts). */
class PlatformPage {
  constructor(page, baseURL) {
    this.page = page;
    this.baseURL = baseURL;
    this.editor = new MetadataEditor(page);
    this.suites = new SuitesPage(page);
    this.loginDialog = page.locator('#login');
    this.openLoginDialog = page.locator('#login[open]');
    this.caseTable = page.locator('#platform-cases table');
    this.recipient = page.locator('#recipient');
    this.executeButton = page.locator('#execute');
    this.type = page.locator('#type');
    this.category = page.locator('#category');
    this.automatedCount = page.locator('#automated-count');
    this.history = page.locator('#history-list');
    this.historyButtons = page.locator('#history-list button');
    this.historyRows = page.locator('#history-list .history-row');
    this.runStatus = page.locator('#run-status');
    this.mailStatus = page.locator('#mail-status');
    this.delivery = page.locator('#delivery');
    this.casesMode = page.locator('input[name=mode][value=cases]');
    this.categoryMode = page.locator('input[name=mode][value=category]');
    this.catalogSearch = page.getByLabel('Buscar catálogo');
  }

  static async authenticate(context, baseURL, token) {
    const response = await context.request.post(baseURL + '/api/session', { data: { token } });
    assert.equal(response.status(), 200, 'La sesión de validación QA debe establecerse');
    return response;
  }

  async open() { return this.page.goto(this.baseURL); }
  async openCatalog() {
    await this.page.getByRole('tab', { name: 'Catálogo y requisitos', exact: true }).click();
  }
  async openSuites() {
    await this.page.getByRole('tab', { name: 'Diseñador de suites', exact: true }).click();
  }
  async newCase() {
    await this.page.getByRole('button', { name: 'Nuevo caso', exact: true }).click();
  }
  async logout() {
    await this.page.getByRole('button', { name: 'Cerrar sesión', exact: true }).click();
    await this.openLoginDialog.waitFor();
  }
  async fitsViewport() {
    return this.page.evaluate(() => document.documentElement.scrollWidth <= innerWidth);
  }
  async useMobileViewport() { await this.page.setViewportSize({ width: 390, height: 844 }); }
}

module.exports = { PlatformPage };
