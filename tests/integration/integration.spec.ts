import { test, expect } from '@playwright/test';
import { loginAs } from '../../utils/qa';
test('QA-INT-01 Login y recurso protegido integran sesión y backend', async ({ page }) => {
 await loginAs(page, 'admin');
 const response = await page.request.get('/admin/reportes');
 expect(response.status()).toBe(200);
 expect(response.headers()['content-type']).toContain('text/html');
 expect(await response.text()).toMatch(/reportes/i);
});
