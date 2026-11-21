import { expect, Page } from '@playwright/test';

export class RegistrationPage {
  constructor(readonly page: Page) {}
  get form() { return this.page.locator('form[action$="/register"]'); }
  get surname() { return this.form.locator('[name="Apellido"]'); }
  async isSurnameValid() {
    return this.surname.evaluate((el: HTMLInputElement) => el.checkValidity());
  }
  async csrfToken() {
    return this.form.locator('input[name="_token"]').inputValue();
  }
  async open() {
    const response = await this.page.goto('/login', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await this.page.waitForFunction('typeof window.bootstrap?.Tab === "function"', undefined, { timeout: 30000 });
    await this.page.locator('a[data-bs-toggle="tab"][href="#register"]').click();
    await expect(this.form).toBeVisible();
  }
  async fill(surname: string, email: string, document: string) {
    for (const [name, value] of Object.entries({ Nombre:'Pedro', Apellido:surname, Email:email,
      CedulaIdentidad:document, FechaNacimiento:'2010-05-05', Peso:'60', Altura:'1.62' })) {
      await this.form.locator(`[name="${name}"]`).fill(value);
    }
    for (const [name,value] of Object.entries({ Nacionalidad:'Bolivia', Genero:'M', Categoria:'Junior', Nivel:'Principiante' })) {
      const field=this.form.locator(`[name="${name}"]`);
      if (await field.evaluate(el => el.tagName === 'SELECT')) await field.selectOption(value);
      else if (await field.getAttribute('type') === 'hidden') await expect(field).toHaveValue(value);
      else await field.fill(value);
    }
  }
  async submit() {
    const response=this.page.waitForResponse(r=>new URL(r.url()).pathname==='/register' && r.request().method()==='POST');
    await this.form.getByRole('button', { name:/enviar postulación/i }).click();
    return response;
  }
}
