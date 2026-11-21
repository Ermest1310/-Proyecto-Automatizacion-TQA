import { loginAs } from '../../utils/qa';
import { test, expect } from '@playwright/test';

test.describe('Regresión - BUG-003: Protección de rutas administrativas', () => {
  test('Tras corregir el defecto, el admin conserva acceso autorizado', async ({ page }) => {

    await loginAs(page, 'admin');
    await expect(page).toHaveURL(/admin\/inicio/);
  });
});
