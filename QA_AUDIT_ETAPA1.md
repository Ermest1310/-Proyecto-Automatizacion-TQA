# Auditoría QA — Matriz de los 18 tests (Ejecución Chromium)

Fecha: 12/11/2026
Contexto: Tras ETAPA 1-4 (servidor Laravel corregido, BD de pruebas, usuarios QA, Playwright configurado).

## Resultado global

- Tests ejecutados: 18
- PASS: 7
- FAIL: 11
- Aprobación: 38.9% · Fallos: 61.1%

## Matriz de auditoría

| ID | Escenario | Archivo | Ruta / Endpoint | Resultado esperado | Resultado obtenido | Causa real del fallo | Clasificación |
|---|---|---|---|---|---|---|---|
| QA-01 | Login correcto (admin) | tests/authentication/authentication.spec.ts | POST /login → redirección | URL /dashboard | FAIL → final /login (o timeout goto) | Login no concluyó; expectativa /dashboard vs real /admin/inicio (AuthController) | Error de test / inconclusivo |
| QA-02 | Login inválido muestra error | tests/authentication/authentication.spec.ts | POST /login | .alert-danger visible | FAIL → timeout goto /login | Timeout en navegación / posible inestabilidad | Error de test |
| QA-03 | Usuario sin rol admin/juez → /admin denegado | tests/authorization/authorization.spec.ts | GET /admin (guest) | 401/403/404 | PASS (403/404) | OK: denegación verificada | PASS |
| QA-04 | Juez accede al panel de revisión | tests/authorization/authorization.spec.ts | login juez → GET /juez/dashboard | 200 | FAIL → timeout goto /login | Timeout / login juez no concluyó | Error de test |
| QA-05 | Login exitoso redirige a dashboard | tests/functional/functional.spec.ts | POST /login (admin) | /dashboard | FAIL → final /login | Real: Administrador → /admin/inicio | Error de test (expectativa) |
| QA-06 | Logout cierra sesión | tests/functional/functional.spec.ts | POST /logout | /login | FAIL → final /login | No concluyó flujo de autenticación | Inconclusivo |
| QA-07 | Integración auth sin errores backend | tests/integration/integration.spec.ts | GET /login | < 500 | PASS | Débil: solo valida status < 500 | PASS débil |
| QA-08 | Regresión BUG-003: admin autorizado | tests/regression/regression.spec.ts | login admin → GET /admin | 200 | FAIL → final /login | Login no concluyó / expectativa | Error de test |
| QA-BUG-001 | Diagnóstico expuesto (visitante) | tests/security/security.spec.ts | GET /diagnostico | Acceso denegado | PASS (404) | Ruta /diagnostico no encontrada en web.php → 404 inconcluso | Inconclusivo |
| QA-BUG-002 | Sanitización caracteres válidos | tests/security/security.spec.ts | GET /perfil (admin) | Sin falsos positivos | PASS (200/403) | Débil: no ejercita sanitización real | Inconclusivo |
| QA-BUG-003 | Autorización /admin (sin rol) | tests/security/security.spec.ts | GET /admin (guest) | Denegar | PASS (403/404) | OK | PASS |
| QA-BUG-004 | Protección de .env | tests/security/security.spec.ts | GET /.env | Denegar/no acceder | PASS (404) | Ruta inexistente → inconclusive | Inconclusivo |
| QA-BUG-005 | Gimnasta no aprueba solicitudes | tests/security/security.spec.ts | POST /api/level-requests/1/approve | 401/403 | FAIL (404) | Ruta placeholder → 404 no prueba autorización | Error de test |
| QA-BUG-006 | Chatbot: errores sin trazas | tests/security/security.spec.ts | POST /psicologia/chat (real) / placeholder | Sin stack/Exception | FAIL (selector timeout) | Selector placeholder | Error de test |
| QA-BUG-007 | Endpoints IA: autenticación | tests/security/security.spec.ts + tests/api/api.spec.ts | GET /api/ai/status | 401/403 | FAIL (404) | Ruta placeholder → real IA: /ia/predecir-deduccion, /ia/registrar-feedback, /ia/estadisticas-modelo, /sepi/diagnosticar | Error de test |
| QA-BUG-008 | Carga de archivos PHP | tests/security/security.spec.ts | upload /tmp/shell_test.php | Rechazar/validar | FAIL (ENOENT) | Archivo placeholder | Error de test |

## 7. Bugs con fallo por error de test/configuración (no defectos de app)

- BUG-005: endpoint usado (`/api/level-requests/1/approve`) no existe → 404.
- BUG-006: selector chatbot placeholder (`textarea[name="message"]`) no existe.
- BUG-007: endpoint usado (`/api/ai/status`) no existe → 404; reales: `/ia/predecir-deduccion`, `/ia/registrar-feedback`, `/ia/estadisticas-modelo`, `/sepi/diagnosticar`.
- BUG-008: archivo placeholder `/tmp/shell_test.php`.
- QA-05/QA-08: expectativa `/dashboard` vs redirect real `/admin/inicio`.

## 8. Pendiente para ETAPA 2 (correcciones justificadas)

- Corregir rutas/selectores en tests.
- No modificar código Laravel hasta autorización.
