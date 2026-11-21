import { test, expect } from '@playwright/test';
import { LoginPage } from '../../pages/LoginPage';
import { DashboardPage } from '../../pages/DashboardPage';
import { TEST_CREDENTIALS } from '../../config/test-data';

test.describe('Autenticación', () => {
  test('Un usuario registrado puede iniciar sesión correctamente', async ({ page }) => {
    const login = new LoginPage(page);
    const dashboard = new DashboardPage(page);

    await login.navigate();
    await login.login(TEST_CREDENTIALS.admin.email, TEST_CREDENTIALS.admin.password);
    await dashboard.expectAuthenticated(/admin\/inicio/);
  });

  test('Credenciales inválidas muestran un mensaje de error', async ({ page }) => {
    const login = new LoginPage(page);

    await login.navigate();
    await login.login('usuario_invalido@qa.local', 'passwordIncorrecta');
    await expect(login.errorMessage).toBeVisible();
  });
});
