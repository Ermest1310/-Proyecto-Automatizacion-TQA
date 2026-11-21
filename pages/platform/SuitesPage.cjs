class SuitesPage {
  constructor(page) {
    this.page = page;
    this.root = page.locator('#platform-suites');
    this.recipient = page.getByLabel('Correo para suites', { exact: true });
  }
  async create() { await this.page.getByRole('button', { name: 'Nueva suite', exact: true }).click(); }
  async preview(id) { await this.page.getByRole('button', { name: 'Plan ' + id, exact: true }).click(); }
  async execute(id) {
    const response = this.page.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/api/platform/suites/' + id + '/execute');
    await this.page.getByRole('button', { name: 'Ejecutar suite ' + id, exact: true }).click();
    return response;
  }
  async openResults() { await this.root.getByRole('button', { name: 'Ver resultados', exact: true }).click(); }
}

module.exports = { SuitesPage };
