import { test, expect } from '@playwright/test';
import { loginAs, anonymousIA } from '../../utils/qa';
import { ChatbotPage } from '../../pages/ChatbotPage';
import { backendRegression } from '../../utils/BackendRegression';
test('BUG-001 Script original de diagnóstico no expone datos', async ({ request }) => {
 const response = await request.get('/test_postulaciones.php', { maxRedirects: 0 });
 expect([403,404]).toContain(response.status());
 expect(await response.text()).not.toMatch(/APP_KEY=|DB_PASSWORD=|qa[^\s<>]*@|CedulaIdentidad/);
 // 404 es válido aquí: el defecto original era un archivo público eliminado.
});
test('BUG-002 Ampersand legítimo conserva integridad (TC-03)', async ({}, info) => {
 await backendRegression('002', info);
});
test('BUG-003 Gimnasta no obtiene reportes, PDF ni panel juez', async ({ page }) => {
 await loginAs(page, 'athlete');
 for (const endpoint of ['/admin/reportes','/admin/reportes/exportar-pdf','/juez/dashboard']) {
  const response = await page.request.get(endpoint, { headers: { Accept: 'application/json' }, maxRedirects: 0 });
  expect(response.status(), endpoint).toBe(403);
  expect(await response.json()).toEqual({ error: 'Sin permisos' });
 }
});
test('BUG-004 Traversal autenticado en descarga de backups (TC-06)', async ({ page }, info) => {
 await loginAs(page, 'admin');
 const control = await page.request.get('/admin/reportes'); expect(control.status()).toBe(200);
 const response = await page.request.get('/admin/reportes/descargar-backup/..%5C..%5C..%5C.env', { maxRedirects: 0 });
 expect(response.status()).toBe(404);
 expect(response.headers()['content-disposition']).toBeUndefined();
 expect(await response.text()).not.toMatch(/APP_KEY=|DB_PASSWORD=|DB_DATABASE=/);
 await backendRegression('004', info);
});
test('BUG-005 Gimnasta no aprueba solicitud ajena pendiente (TC-07)', async ({}, info) => {
 await backendRegression('005', info);
});
test('BUG-006 Error real del chatbot no expone trazas', async ({ page }, info) => {
 await loginAs(page, 'admin');
 const response = await page.goto('/areamedica/psicologia/chatbot', { waitUntil: 'domcontentloaded' });
 expect(response?.status()).toBe(200);
 const chatbot = new ChatbotPage(page);
 const reply = page.waitForResponse(r => new URL(r.url()).pathname === '/api/psicologia/chat' && r.request().method() === 'POST');
 await chatbot.sendMessage('Consejo breve de concentración deportiva');
 const result = await reply;
 expect([200,502,503]).toContain(result.status());
 const body = await result.json();
 expect(Object.keys(body)).toEqual(['response']);
 expect(body.response).toEqual(expect.any(String));
 expect(body.response.length).toBeGreaterThan(0);
 expect(body.response).not.toMatch(/Exception|Stack trace|vendor[\\/]|APP_KEY|sk-[a-zA-Z0-9]|cURL error|class .*not found/i);
 await chatbot.expectBotReply(body.response);
 await backendRegression('006', info);
});
test('BUG-007 IA GET exige autenticación', async ({ request }) => {
 await anonymousIA(request, '/api/ia/estadisticas-modelo');
});
test('BUG-008 Juez rechaza PHP en endpoint original (TC-11)', async ({}, info) => {
 await backendRegression('008', info);
});
