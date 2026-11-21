import { expect, Page, APIRequestContext } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { TEST_CREDENTIALS } from '../config/test-data';
export function blocked(reason: string): never { throw new Error('BLOCKED: ' + reason); }
export async function loginAs(page: Page, role: keyof typeof TEST_CREDENTIALS) {
  const login = new LoginPage(page);
  const response = await login.navigate();
  if (response?.status() !== 200) blocked('Login no disponible: ' + response?.status());
  await login.login(TEST_CREDENTIALS[role].email, TEST_CREDENTIALS[role].password);
  await page.waitForURL(url => url.pathname !== '/login', { timeout: 12000 }).catch(() => blocked('No se confirmó sesión QA de rol ' + role));
  const target = { admin: '/admin/inicio', judge: '/juez/dashboard', athlete: '/gimnasta/dashboard', guest: '/' }[role];
  const probe = await page.request.get(target, { maxRedirects: 0 });
  if (probe.status() !== 200) blocked('Sesión QA no confirmada: ' + probe.status());
}
export async function anonymousIA(request: APIRequestContext, endpoint: string, method = 'GET') {
  // Obtener sesión/CSRF para que un POST no se apruebe por un 419.
  const login = await request.get('/login');
  expect(login.status()).toBe(200);
  const html = await login.text();
  const token = html.match(/name="_token"\s+value="([^"]+)"/)?.[1];
  if (!token) blocked('No se obtuvo CSRF para petición anónima');
  const response = await request.fetch(endpoint, { method, maxRedirects: 0,
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
    ...(method === 'POST' ? { data: {} } : {}) });
  expect(response.status(), endpoint).toBe(401);
  expect(await response.json()).toEqual({ error: 'Debes iniciar sesión con una cuenta activa.' });
}

