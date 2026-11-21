/** Shared editor used by cases, requirements and suites. */
class MetadataEditor {
  constructor(page) {
    this.root = page.locator('#metadata-editor');
    this.identifier = this.root.getByLabel('Identificador', { exact: true });
    this.name = this.root.getByLabel('Nombre', { exact: true });
    this.title = this.root.getByLabel('Título', { exact: true });
    this.reason = this.root.getByLabel('Motivo del cambio', { exact: true });
    this.category = this.root.getByLabel('Categoría', { exact: true });
    this.caseIds = this.root.getByLabel('Casos seleccionados (IDs, uno por línea)', { exact: true });
    this.requirementIds = this.root.getByLabel('Requisitos vinculados (IDs, uno por línea)', { exact: true });
    this.caseSearch = this.root.getByLabel('Buscar opciones de Casos', { exact: true });
  }

  async save() {
    await this.root.getByRole('button', { name: 'Guardar revisión', exact: true }).click();
    await this.root.waitFor({ state: 'hidden' });
  }
  async close() { await this.root.getByRole('button', { name: 'Cerrar editor', exact: true }).click(); }
  async chooseCase(id) {
    await this.root.getByText('Elegir casos', { exact: true }).click();
    await this.caseSearch.fill(id);
    await this.root.getByRole('checkbox', { name: new RegExp(id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')) }).check();
  }
}

module.exports = { MetadataEditor };
