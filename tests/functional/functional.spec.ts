import { test, expect } from '@playwright/test';
import { LoginPage } from '../../pages/LoginPage';
import { DashboardPage } from '../../pages/DashboardPage';
import { TEST_CREDENTIALS } from '../../config/test-data';

test.describe('Funcionalidad - Flujos principales', () => {
  test('El login exitoso redirige al panel del administrador', async ({ page }) => {
    const login = new LoginPage(page);
    const dashboard = new DashboardPage(page);

    await login.navigate();
    await login.login(TEST_CREDENTIALS.admin.email, TEST_CREDENTIALS.admin.password);
    await dashboard.expectAuthenticated(/admin\/inicio/);
  });

  test('El logout cierra la sesión y vuelve al login', async ({ page }) => {
    const login = new LoginPage(page);
    const dashboard = new DashboardPage(page);

    await login.navigate();
    await login.login(TEST_CREDENTIALS.admin.email, TEST_CREDENTIALS.admin.password);
    await dashboard.expectAuthenticated(/admin\/inicio/);
    await dashboard.logout();
    await expect(page).toHaveURL(/\/login/);
    const protectedResponse = await page.request.get('/admin/inicio', { headers: { Accept: 'application/json' }, maxRedirects: 0 });
    expect(protectedResponse.status()).toBe(401);
  });
});

