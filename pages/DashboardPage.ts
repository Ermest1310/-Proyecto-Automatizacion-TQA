import { Navigation } from '../components/Navigation';
import { Page, Locator } from '@playwright/test';
import { expect } from '@playwright/test';

export class DashboardPage {
  readonly page: Page;
  readonly welcomeMessage: Locator;
  readonly navigation: Navigation;

  constructor(page: Page) {
    this.page = page;
    this.welcomeMessage = page.locator('.hero-welcome');
    // El botón de cierre de sesión vive dentro del dropdown del navbar (Bootstrap 4).
    this.navigation = new Navigation(page);
  }

  async expectAuthenticated(expectedUrlPattern: RegExp) {
    await expect(this.page).toHaveURL(expectedUrlPattern);
    await expect(this.welcomeMessage).toBeVisible();
  }

  async logout() {
    await this.navigation.logout();
  }
}
