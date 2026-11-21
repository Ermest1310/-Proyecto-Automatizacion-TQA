import { expect, Page } from '@playwright/test';

/** Navigation shared by authenticated Laravel panels. */
export class Navigation {
  constructor(private readonly page: Page) {}
  get accountToggle() { return this.page.locator('nav.main-header a[data-toggle="dropdown"]'); }
  get logoutButton() { return this.page.locator('form[action$="/logout"] button[type="submit"]'); }

  async logout() {
    // The Blade loads Bootstrap/jQuery after the panel has appeared.
    await this.page.waitForLoadState('load', { timeout: 30000 });
    await this.page.waitForFunction('typeof window.jQuery?.fn?.dropdown === "function"', undefined, { timeout: 15000 });
    await this.accountToggle.click();
    await expect(this.logoutButton).toBeVisible();
    await this.logoutButton.click();
  }
}
