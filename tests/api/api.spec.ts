import { TEST_CREDENTIALS } from '../../config/test-data';
import { test, expect } from '@playwright/test';
import { LoginPage } from '../../pages/LoginPage';
import { anonymousIA } from '../../utils/qa';
test('QA-API-01 Login real rechaza contraseña incorrecta', async ({ page }) => {
 const login = new LoginPage(page); await login.navigate();
 await login.login(TEST_CREDENTIALS.admin.email, 'incorrecta');
 await expect(login.errorMessage).toContainText(/Credenciales incorrectas/i);
 await expect(page).toHaveURL(/\/login$/);
});
test('QA-API-02 BUG-007 IA POST exige autenticación', async ({ request }) => {
 for (const endpoint of ['/api/ia/predecir-deduccion', '/api/ia/registrar-feedback', '/api/sepi/diagnosticar'])
   await anonymousIA(request, endpoint, 'POST');
});
