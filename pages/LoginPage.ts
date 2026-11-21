import { Page, Locator } from '@playwright/test';
import { Response } from 'playwright';

export class LoginPage {
  readonly page: Page;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly loginButton: Locator;
  readonly errorMessage: Locator;
  readonly nacionalidadSelect: Locator;
  readonly cedulaInput: Locator;

  constructor(page: Page) {
    this.page = page;

    // Selectores reales tomados de resources/views/auth/login.blade.php
    this.emailInput = page.locator('input[name="username"]');
    this.nacionalidadSelect = page.locator('#nacionalidadSelect');
    this.cedulaInput = page.locator('#documentoInput');
    this.passwordInput = page.locator('input[name="password"]');
    this.loginButton = page.getByRole('button', { name: /autenticar ingreso/i });
    this.errorMessage = page.locator('.alert-danger');
  }

  async navigate(): Promise<Response | null> {
    return await this.page.goto('/login', { waitUntil: 'domcontentloaded' });
  }

  async login(
    email: string,
    password: string,
    nacionalidad: string = 'Venezuela',
    cedula: string = '12345678',
    retry = true,
  ) {
    await this.emailInput.fill(email);
    await this.nacionalidadSelect.selectOption(nacionalidad);
    await this.cedulaInput.fill(cedula);
    await this.passwordInput.fill(password);
    const responsePromise = this.page.waitForResponse(r => new URL(r.url()).pathname === '/login' && r.request().method() === 'POST');
    await this.loginButton.click();
    const response = await responsePromise;
    if (response.status() === 429 && retry) {
      const seconds = Number(response.headers()['retry-after']);
      if (!Number.isFinite(seconds) || seconds < 0 || seconds > 60) throw new Error('BLOCKED: Login limitado; Retry-After no utilizable');
      console.log('QA: respetando Retry-After del login: ' + seconds + 's');
      await new Promise(resolve => setTimeout(resolve, seconds * 1000 + 1000));
      await this.navigate();
      await this.login(email, password, nacionalidad, cedula, false);
      return;
    }
    if (response.status() === 429) throw new Error('BLOCKED: Login continúa limitado tras Retry-After');
  }

  async getErrorMessage() {
    return await this.errorMessage.textContent();
  }
}

