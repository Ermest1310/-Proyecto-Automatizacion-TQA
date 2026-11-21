# Informe de auditoría y ejecución QA — 12/11/2026

Se aplicaron correcciones exclusivamente a automatización en E:\qa. No se modificó código Laravel, reglas de negocio ni configuración/estructura de la base original. Las autenticaciones generan sesiones y auditoría normales en la BD configurada de pruebas; no se ejecutaron migraciones, limpieza, registro de usuarios ni aprobaciones. Se verificó DB_DATABASE=gimnasia_unificada_test y ausencia de config.php cacheado antes de ejecutar.

## Resultados

Histórico informado: 7 PASS / 11 FAIL. No es una comparación idéntica: los tests actuales ya tenían cambios y se corrigió su cobertura semántica.

chromium: {"PASS":15,"FAIL":0,"BLOCKED":3,"NOT RUN":0}; 18 casos.

firefox: {"PASS":15,"FAIL":0,"BLOCKED":3,"NOT RUN":0}; 18 casos.

webkit: {"PASS":15,"FAIL":0,"BLOCKED":3,"NOT RUN":0}; 18 casos.

Playwright no tiene estado BLOCKED nativo: los bloqueos arrojan error explícito BLOCKED y mantienen código de salida no cero; esta clasificación se deriva del JSON. No se usaron skip ni expect.soft. Un PASS de prueba no cierra automáticamente un defecto.

## Matriz de los 18 tests de Chromium

| ID | Escenario | Archivo | Ruta | Esperado | Obtenido | Diagnóstico y clasificación |
|---|---|---|---|---|---|---|
| QA-API-01 | QA-API-01 Login real rechaza contraseña incorrecta | api/api.spec.ts | POST /login | Mensaje de credenciales incorrectas; continúa en login | PASS | Error del test: /api/login inexistente y status >=400 aceptaba 404 — criterio observado; defecto no cerrado automáticamente |
| QA-API-02 | QA-API-02 BUG-007 IA POST exige autenticación | api/api.spec.ts | POST /api/ia/predecir-deduccion, /api/ia/registrar-feedback, /api/sepi/diagnosticar | 401 JSON de autenticación con CSRF válido | PASS | Error del test: endpoint placeholder; no aceptar 419/404 — criterio observado; defecto no cerrado automáticamente |
| QA-AUTH-01 | Un usuario registrado puede iniciar sesión correctamente | authentication/authentication.spec.ts | POST /login → /admin/inicio | Panel admin y .hero-welcome visibles | PASS | Expectativa /dashboard incorrecta en histórico — criterio observado; defecto no cerrado automáticamente |
| QA-AUTH-02 | Credenciales inválidas muestran un mensaje de error | authentication/authentication.spec.ts | POST /login | Mensaje de error visible | PASS | Histórico timeout; navegación esperaba recursos externos — criterio observado; defecto no cerrado automáticamente |
| QA-AUTHZ-01 | Un usuario sin rol administrativo recibe acceso denegado a /admin/reportes | authorization/authorization.spec.ts | GET /admin/reportes (gimnasta) | 403 con sesión confirmada | PASS | Error del test: /admin inexistente; guest sin sesión confirmada — criterio observado; defecto no cerrado automáticamente |
| QA-AUTHZ-02 | Un usuario con rol de juez conserva acceso al panel de revisión | authorization/authorization.spec.ts | POST /login → /juez/dashboard | Panel juez accesible con sesión confirmada | PASS | Helper inicial exigía 200 en / que redirige por rol; corregido — criterio observado; defecto no cerrado automáticamente |
| QA-FUNC-01 | El login exitoso redirige al panel del administrador | functional/functional.spec.ts | POST /login → /admin/inicio | Panel admin visible | PASS | Error histórico de expectativa — criterio observado; defecto no cerrado automáticamente |
| QA-FUNC-02 | El logout cierra la sesión y vuelve al login | functional/functional.spec.ts | POST /logout | Vuelve a /login | PASS | Selector relativo no coincidía con action absoluto; 429 intermitente previo — criterio observado; defecto no cerrado automáticamente |
| QA-INT-01 | QA-INT-01 Login y recurso protegido integran sesión y backend | integration/integration.spec.ts | POST /login; GET /admin/reportes | Sesión efectiva y recurso HTML 200 | PASS | Error del test: antes solo GET /login <500 — criterio observado; defecto no cerrado automáticamente |
| QA-REG-01 | Tras corregir el defecto, el admin conserva acceso autorizado | regression/regression.spec.ts | POST /login → /admin/inicio | Control positivo admin autorizado | PASS | 429 por límite de intentos; POM respeta Retry-After — criterio observado; defecto no cerrado automáticamente |
| BUG-001 | BUG-001 Script original de diagnóstico no expone datos | security/security.spec.ts | GET /test_postulaciones.php | 403/404 sin datos de usuarios ni configuración | PASS | Error del test: /diagnostico no era el recurso original — criterio observado; defecto no cerrado automáticamente |
| BUG-002 | BUG-002 Ampersand legítimo conserva integridad (TC-03) | security/security.spec.ts | POST /register; TC-03 | Nombre con & íntegro; sin rechazo genérico del sanitizador | BLOCKED | Bloqueado: requiere fixture persistente aislada; regex actual excluye & — Error: BLOCKED: TC-03 requiere registro y verificación persistida en BD aislada. El controlador actual además rechaza & por regex de Apellido; decisión de negocio pendiente. No sustituir este caso por XSS.     at ..\utils\qa.ts:4    2 / import { LoginPage } from '../pages/LoginPage';   3 / import { TEST_CREDENTIALS } from '../config/test-data'; > 4 / export function blocked(reason: string): never { throw new Error('BLOCKED: ' + reason); }     /                                                        ^   5 / export async function loginAs(page: Page, role: keyof typeof TEST_CREDENTIALS) {   6 /   |
| BUG-003 | BUG-003 Gimnasta no obtiene reportes, PDF ni panel juez | security/security.spec.ts | GET /admin/reportes, /admin/reportes/exportar-pdf, /juez/dashboard | 403 JSON Sin permisos para gimnasta | PASS | Error del test: /admin 404 no demostraba autorización — criterio observado; defecto no cerrado automáticamente |
| BUG-004 | BUG-004 Traversal autenticado en descarga de backups (TC-06) | security/security.spec.ts | GET /admin/reportes/descargar-backup/..%5C..%5C..%5C.env; TC-06 | 404 sin descarga ni secretos, control admin 200 | PASS | Error del test: /.env directo no reproducía traversal — criterio observado; defecto no cerrado automáticamente |
| BUG-005 | BUG-005 Gimnasta no aprueba solicitud ajena pendiente (TC-07) | security/security.spec.ts | POST /admin/solicitudes-nivel/{id}/aprobar; TC-07 | 403; solicitud ajena y nivel sin cambios | BLOCKED | Bloqueado: faltan solicitud pendiente controlada y verificación BD antes/después — Error: BLOCKED: Requiere solicitud ajena pendiente, BD aislada y lectura antes/después. No enviar aprobación a un ID inventado: una regresión podría modificar datos. Ruta actual POST /admin/solicitudes-nivel/{id}/aprobar; la antigua ruta entrenador fue retirada.     at ..\utils\qa.ts:4    2 / import { LoginPage } from '../pages/LoginPage';   3 / import { TEST_CREDENTIALS } from '../config/test-data'; > 4 / export function blocked(reason: string): never { throw new Error('BLOCKED: ' + reason); }     /                                                        ^   5 / export async function loginAs(p |
| BUG-006 | BUG-006 Error real del chatbot no expone trazas | security/security.spec.ts | GET /areamedica/psicologia/chatbot; POST /api/psicologia/chat | Error real 502/503 con mensaje seguro visible | PASS | Error del test: solo observaba eco del usuario; ahora valida respuesta de servidor — criterio observado; defecto no cerrado automáticamente |
| BUG-007 | BUG-007 IA GET exige autenticación | security/security.spec.ts | GET /api/ia/estadisticas-modelo | 401 JSON de autenticación | PASS | Error del test: ruta placeholder y redirecciones sin verificar — criterio observado; defecto no cerrado automáticamente |
| BUG-008 | BUG-008 Juez rechaza PHP en endpoint original (TC-11) | security/security.spec.ts | POST /juez/store; TC-11 | 422 con errors.video; sin persistencia ni exposición pública | BLOCKED | Bloqueado para cierre: faltan IDs válidos y verificación almacenamiento; /analisis/upload no era BUG-008 — Error: BLOCKED: Rechazo específico del archivo verificado; faltan IDs válidos de gimnasta/competencia/aparato y comprobación de no persistencia para cerrar TC-11.     at ..\utils\qa.ts:4    2 / import { LoginPage } from '../pages/LoginPage';   3 / import { TEST_CREDENTIALS } from '../config/test-data'; > 4 / export function blocked(reason: string): never { throw new Error('BLOCKED: ' + reason); }     /                                                        ^   5 / export async function loginAs(page: Page, role: keyof typeof TEST_CREDENTIALS) {   6 /   const login = new LoginPage(page);   7 /   |

## Criterios de regresión y cierre de BUG-001 a BUG-008

| BUG | Demuestra regresión | Evidencia necesaria para cerrar | Alcance actual |
|---|---|---|---|
| 001 | Script original devuelve datos públicos / HTTP 200 | Recurso retirado o acceso denegado, sin PII | HTTP real sobre ruta original; 404 es válido para archivo retirado, no para una ruta inventada |
| 002 | Ampersand legítimo bloqueado genéricamente o datos alterados | Registro controlado con & persistido íntegro y política acordada | BLOCKED; controlador actual Apellido regex excluye &, requiere aclarar requisito frente al plan original |
| 003 | Gimnasta obtiene 200/datos admin/juez | Sesión de gimnasta verificada, denegación 403 y control admin/juez positivo | Cubre reportes, PDF y panel juez; no toda la matriz de permisos |
| 004 | Traversal devuelve .env o descarga | Admin autenticado, URL original rechazada y ningún secreto | HTTP real y revisión de guardas basename/.. y whitelist; pendiente control positivo de descarga de backup válido |
| 005 | Aprobación por gimnasta o cambio de estado/nivel | Solicitud ajena pendiente y estado/nivel antes/después iguales | BLOCKED; no se envía POST contra ID inventado; ruta entrenador retirada, actual admin identificada |
| 006 | Error expone stack, Exception, paths, claves o cURL | Fallo real 502/503 seguro con UI mostrando respuesta del servidor | Valida error observado; no incluye todos los fallos de proveedor mediante inyección determinista |
| 007 | Anónimo obtiene 200/datos, pasa autenticación | 401 JSON para GET y los tres POST con CSRF válido | Las cuatro rutas reales revisadas; regresión lógica detectada por aserción estricta; no se mutó producción |
| 008 | PHP aceptado/persistido o servido públicamente | Rechazo específico video con IDs válidos, sin fila/archivo, y control video real | BLOCKED parcial: solo rechazo de archivo; falta fixture de negocio y verificación de no persistencia |

No se ejecutó mutation testing del servidor: las condiciones anteriores describen qué rechazarían las aserciones. BUG-002/005/008 aún no tienen regresión completa ejecutable; sus bloqueos son explícitos y no equivalen a correcciones terminadas.

## Fuentes y trazabilidad

- E:\laragon\www\Proyecto2\docs\entregable1_pruebas_funcionales.md: TC-03, TC-05, TC-06, TC-07, TC-10 y TC-11; otras secciones remiten al TSV original. Se conservan los identificadores BUG; no se inventa una correspondencia para escenarios sin ID único.
- docs/analisis_proyecto.md: catálogo original de ocho BUG.
- routes/web.php y routes/api.php: métodos y middleware reales. IA usa web + auth.custom. POST /api/ia/predecir-deduccion exige permission:videos.create; POST /api/ia/registrar-feedback exige permission:juez.edit; GET /api/ia/estadisticas-modelo exige permission:juez.view; POST /api/sepi/diagnosticar y POST /api/psicologia/chat exigen permission:areamedica.view. POST /admin/solicitudes-nivel/{id}/aprobar y /rechazar usan auth.custom + permission:dashboard.admin.
- AuthController, JuezController::storeEvaluation, VideoAnalysisController::uploadVideo, ReporteController::descargarBackup y SportsChatService.
- Blade real: navbar form[action$="/logout"], chatbot #user-input / #send-button / #chat-messages; login username, nacionalidadSelect y documentoInput.

## Archivos modificados

playwright.config.ts; pages/LoginPage.ts; pages/DashboardPage.ts; utils/qa.ts; tests/api/api.spec.ts; tests/authorization/authorization.spec.ts; tests/integration/integration.spec.ts; tests/regression/regression.spec.ts; tests/security/security.spec.ts; tests/functional/functional.spec.ts; QA_DIAGNOSTICO_PREVIO.md; QA_REPRODUCIR.md. Además este informe y evidencias nuevas. Originales conservados en reports/backup-20261112-codex.

## Hallazgos y continuación

1. BUG-002 mantiene discrepancia de requisito: el sanitizador ya no reescribe entradas, pero Apellido excluye & por regex. Propuesta separada: acordar caracteres admitidos y ajustar validación solo con autorización. No se afirma una vulnerabilidad nueva sin reproducir persistencia.
2. Completar fixtures aisladas para registro &, solicitud ajena pendiente y evaluación con atleta inscrito/competencia/aparato válidos. Añadir comprobaciones BD/almacenamiento de solo lectura antes/después. No ejecutar contra BD original.
3. Incorporar video válido controlado y verificación de almacenamiento privado; el fixture PHP es inerte, sin comandos ni código ejecutable. El cierre BUG-008 requiere también detectar aceptación indebida cuando el resto del formulario es válido.
4. Conservar throttling: POM lee Retry-After y reintenta solo una vez, sin desactivar seguridad. Workers=1 reduce presión. Login navega con domcontentloaded para no depender de recursos externos.
5. CI pendiente: no integrar GitHub Actions hasta completar regresiones bloqueadas y lograr ejecución reproducible. La suite actualmente mantiene salida no cero por bloqueos.

## Reproducción y evidencias

Chromium: node node_modules/@playwright/test/cli.js test --project=chromium

Firefox/WebKit: establecer PLAYWRIGHT_BROWSERS_PATH al directorio descargado documentado en QA_REPRODUCIR.md y QA_REPORT_DIR=reports/cross; ejecutar --project=firefox --project=webkit.

En E:\qa las evidencias se conservan bajo reports/validacion-20261112-codex, sin sobrescribir las anteriores. Dentro de esa carpeta: Reportes HTML: reports/html/index.html y reports/cross/html/index.html. JSON: reports/results.json y reports/cross/results.json. Trazas, capturas, vídeos y error-context en las subcarpetas playwright. Los reportes pueden incluir datos QA y tokens de sesión; conservar localmente.
