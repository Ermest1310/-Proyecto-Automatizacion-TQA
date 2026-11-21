import { loginAs } from '../../utils/qa';
import { test, expect } from '@playwright/test';

test.describe('Autorización', () => {
  test('Un usuario sin rol administrativo recibe acceso denegado a /admin/reportes', async ({ page }) => {

    await loginAs(page, 'athlete');
    const response = await page.goto('/admin/reportes');
    expect([403]).toContain(response?.status() ?? 0);
  });

  test('Un usuario con rol de juez conserva acceso al panel de revisión', async ({ page }) => {

    await loginAs(page, 'judge');
    await expect(page).toHaveURL(/juez\/dashboard/);
  });
});

