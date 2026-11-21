# Validación QA — segunda revisión, 12/11/2026

Se completaron los tres escenarios anteriormente bloqueados sin modificar código de producción, reglas de negocio, base original ni borrar información. Las nuevas pruebas de estado usan Laravel real con SQLite en memoria; almacenamiento, directorio público, sesiones y logs se redirigen a la carpeta de evidencias antes del bootstrap. No se ejecutaron migraciones sobre MySQL.

## Resultado actual

| Proyecto | PASS | FAIL | BLOCKED | NOT RUN |
|---|---:|---:|---:|---:|
| chromium | 17 | 1 | 0 | 0 |
| firefox | 17 | 1 | 0 | 0 |
| webkit | 17 | 1 | 0 | 0 |

Histórico informado: 7 PASS / 11 FAIL. Revisión anterior: 15 PASS / 3 BLOCKED por proyecto. Los resultados actuales corresponden a la última ejecución completa de los 54 casos (18 por proyecto), conservando por separado las ejecuciones intermedias. La comparación no es de una versión idéntica: se corrigieron escenarios que antes aprobaban sin reproducir el defecto. BUG-002 ahora falla por una respuesta real del controlador, no por configuración. La salida no cero se conserva.

**Alcance de navegador:** BUG-002, BUG-005 y BUG-008 son integraciones backend iniciadas por Playwright; repetirlas en cada proyecto no añade cobertura del motor de navegador. BUG-004 y BUG-006 combinan navegador/HTTP con controles backend. Las demás pruebas mantienen POM y/o APIRequestContext. SQLite en memoria y esquemas mínimos no sustituyen validación final del esquema y concurrencia en MySQL de pruebas.

## Matriz de los 18 tests (Chromium)

La columna clasificación describe la causa diagnosticada: en PASS se refiere al problema del test/entorno corregido; en BUG-002 al fallo actual frente al requisito original.

| ID | Escenario / archivo | Ruta | Esperado | Obtenido | Causa | Clasificación |
|---|---|---|---|---|---|---|
| QA-API-01 | QA-API-01 Login real rechaza contraseña incorrecta — tests/api/api.spec.ts | POST /login | Rechazo explícito de credenciales incorrectas | PASS: Error visible; URL /login | Antes /api/login y >=400 aceptaban 404; se usa formulario real | Error del test |
| QA-API-02 | QA-API-02 BUG-007 IA POST exige autenticación — tests/api/api.spec.ts | POST /api/ia/predecir-deduccion, /api/ia/registrar-feedback, /api/sepi/diagnosticar | 401 y JSON de autenticación con CSRF válido | PASS: Tres POST devuelven 401 JSON | Endpoint placeholder sustituido; no se aceptan 404/419/500 | Error del test |
| QA-AUTH-01 | Un usuario registrado puede iniciar sesión correctamente — tests/authentication/authentication.spec.ts | POST /login → /admin/inicio | Panel admin y bienvenida visibles | PASS: Panel admin y bienvenida visibles | Expectativa histórica /dashboard incorrecta | Error del test |
| QA-AUTH-02 | Credenciales inválidas muestran un mensaje de error — tests/authentication/authentication.spec.ts | POST /login | Error visible para credenciales inválidas | PASS: Error visible | Navegación sin depender de load; throttling respetado, sin atribuir todos los timeouts históricos a una sola causa | Problema de entorno |
| QA-AUTHZ-01 | Un usuario sin rol administrativo recibe acceso denegado a /admin/reportes — tests/authorization/authorization.spec.ts | GET /admin/reportes con sesión gimnasta | 403 | PASS: 403 | /admin inexistente no demostraba autorización; se confirma sesión de gimnasta | Error del test |
| QA-AUTHZ-02 | Un usuario con rol de juez conserva acceso al panel de revisión — tests/authorization/authorization.spec.ts | POST /login → /juez/dashboard | Panel juez accesible | PASS: Panel juez accesible | Se usa redirección real y se confirma sesión en recurso del rol | Error del test |
| QA-FUNC-01 | El login exitoso redirige al panel del administrador — tests/functional/functional.spec.ts | POST /login → /admin/inicio | Panel admin visible | PASS: Panel admin visible | Expectativa histórica /dashboard corregida | Error del test |
| QA-FUNC-02 | El logout cierra la sesión y vuelve al login — tests/functional/functional.spec.ts | POST /logout; GET /admin/inicio | Login y recurso protegido 401 después del logout | PASS: Login y 401 después de salir | Selector de action absoluto corregido | Error del test |
| QA-INT-01 | QA-INT-01 Login y recurso protegido integran sesión y backend — tests/integration/integration.spec.ts | POST /login; GET /admin/reportes | Sesión efectiva y HTML 200 | PASS: 200 HTML de reportes | Antes solo GET /login <500; ahora recurso protegido tras login | Error del test |
| QA-REG-01 | Tras corregir el defecto, el admin conserva acceso autorizado — tests/regression/regression.spec.ts | POST /login → /admin/inicio | Control positivo de admin autorizado | PASS: Admin autenticado | 429 observado; un reintento respeta Retry-After sin cambiar Laravel | Problema de entorno |
| BUG-001 | BUG-001 Script original de diagnóstico no expone datos — tests/security/security.spec.ts | GET /test_postulaciones.php | 403/404 y ausencia de PII/configuración | PASS: 404 sobre recurso original, sin datos sensibles | Antes /diagnostico no era el recurso original; archivo público retirado | Error del test |
| BUG-002 | BUG-002 Ampersand legítimo conserva integridad (TC-03) — tests/security/security.spec.ts | POST /register; TC-03 | Apellido con & aceptado y persistido íntegro | FAIL: Control sin &: 302 y persistencia; con &: 422 errors.Apellido, sin persistencia | Regex actual de Apellido excluye &; contradice TC-03. El sanitizador actual no es quien rechaza | Defecto de aplicación |
| BUG-003 | BUG-003 Gimnasta no obtiene reportes, PDF ni panel juez — tests/security/security.spec.ts | GET /admin/reportes, /admin/reportes/exportar-pdf, /juez/dashboard | 403 para gimnasta autenticado | PASS: Tres 403 con JSON Sin permisos | Se eliminó aprobación por /admin 404 | Error del test |
| BUG-004 | BUG-004 Traversal autenticado en descarga de backups (TC-06) — tests/security/security.spec.ts | GET /admin/reportes/descargar-backup/{filename}; TC-06 | Traversal rechazado; backup válido descargable | PASS: Control 200 descargable; traversal 404; centinela no expuesto | Antes /.env directo no cubría traversal; ahora control positivo aislado y centinela externo | Error del test |
| BUG-005 | BUG-005 Gimnasta no aprueba solicitud ajena pendiente (TC-07) — tests/security/security.spec.ts | POST /admin/solicitudes-nivel/{id}/aprobar; TC-07 | 403; fila y niveles sin cambios; admin sí aprueba | PASS: 403, fila/estados/niveles idénticos; control admin 302 cambia estado y nivel | Bloqueo incondicional sustituido por solicitud real de otro atleta en SQLite en memoria | Error del test |
| BUG-006 | BUG-006 Error real del chatbot no expone trazas — tests/security/security.spec.ts | POST /api/psicologia/chat y Blade chatbot | Errores sin trazas/secretos, respuesta del servidor visible | PASS: UI muestra respuesta; backend 502/502/503 seguros, sin QA_SECRET | Antes se observaba solo eco; se añaden fallos controlados proveedor/transporte/configuración | Error del test |
| BUG-007 | BUG-007 IA GET exige autenticación — tests/security/security.spec.ts | GET /api/ia/estadisticas-modelo | 401 JSON con rechazo de autenticación | PASS: 401 JSON | Se usa ruta real; los tres POST están cubiertos en QA-API-02 | Error del test |
| BUG-008 | BUG-008 Juez rechaza PHP en endpoint original (TC-11) — tests/security/security.spec.ts | POST /juez/store; TC-11 | PHP rechazado sin fila/archivo; WebM real válido aceptado | PASS: 422 solo errors.video, conteos de filas/discos privado y público iguales; WebM 200 y bytes idénticos en disco privado | Antes formulario incompleto y /analisis/upload no reproducían BUG-008; se añaden IDs e inscripción válidos | Error del test |

## Revisión de los ocho defectos

| BUG | Condición que demuestra regresión | Evidencia para considerar resuelto | Resultado actual y límites |
|---|---|---|---|
| 001 | El script original devuelve 200 con datos de postulaciones | Script eliminado o protegido, sin PII expuesta | PASS: /test_postulaciones.php 404, no una ruta inventada. public del proyecto no contiene el script. No se declara segura cualquier ruta 404 |
| 002 | Un apellido legítimo con & no puede registrarse, o cambia al persistir | Control válido y apellido con & persistidos íntegros | FAIL: 302 con apellido sin &; 422 errors.Apellido con &; sin persistencia del segundo. Discrepancia confirmada contra TC-03 |
| 003 | Gimnasta obtiene datos de admin/juez | Sesión real, 403 para reportes/PDF/panel juez y controles positivos de roles autorizados | PASS en recursos probados; no es auditoría exhaustiva de todos los permisos |
| 004 | Traversal entrega archivo fuera de backups | Descarga válida 200 y traversal 404 sin centinela/secretos ni cabecera de descarga | PASS: control positivo y centinela en almacenamiento aislado; HTTP del despliegue local también rechaza el vector original |
| 005 | Gimnasta aprueba solicitud ajena o cambia estado/nivel | 403; fila completa y niveles antes/después iguales; admin puede aprobar esa misma fixture | PASS: solicitud de usuario 7, atacante 2, aprobador autorizado 1; evidencia de estado y control positivo. Antigua ruta entrenador retirada; ruta actual admin comprobada |
| 006 | Un fallo expone Exception, stack, rutas, cURL o secreto del proveedor | Respuestas públicas mínimas y seguras bajo fallos reales/controlados, mostradas por UI | PASS: UI espera respuesta de servidor; fallos controlados proveedor 500→502, transporte→502 y sin clave→503. Se verifican callbacks de fallo ejecutados; red prohibida en los controles |
| 007 | Anónimo obtiene éxito/datos de IA, o prueba pasa por 404/419 | Los tres POST y el GET devuelven 401 JSON específico con CSRF válido | PASS: aserciones estrictas; 404, 419, errores genéricos y redirecciones no pasan |
| 008 | PHP con datos de negocio válidos se acepta/persiste o queda expuesto | Rechazo solo por video; filas/archivos iguales; video real aceptado en disco privado | PASS: único error video, sin nueva fila/archivo ni cambios en directorio público o disco public; WebM negro real de 16x16 validado por fileinfo y bytes almacenados. Se prueba /juez/store, no se sustituye por /analisis/upload |

**Sensibilidad a regresiones:** las aserciones rechazan estados incorrectos, errores de validación ajenos y cambios indebidos de estado/archivos. Los controles positivos prueban que rutas/fixtures funcionan y que la denegación no se debe a datos inexistentes. BUG-002 demuestra un FAIL real. No se hizo mutation testing del código de producción ni se afirma haber reintroducido empíricamente cada uno de los ocho defectos.

Los controles PHPUnit pasan por kernel HTTP, rutas, controladores y middleware reales, con sesión de test de rol real. Laravel omite CSRF en entorno unitario: estos controles prueban autorización/validación/persistencia, no CSRF del servidor web. Las peticiones anónimas de IA sí verifican el servidor local con token CSRF real.

## Incidencias intermedias conservadas

Firefox presentó un timeout de logout: el botón quedó oculto después de un clic interceptado por la barra lateral. Se corrigió la sincronización del POM, esperando la carga y el plugin de dropdown real antes de interactuar. Un control de Chromium dentro del sandbox no pudo cargar los CDN (ERR_NETWORK_ACCESS_DENIED); su fallo es de entorno y no demuestra fallo de logout de Laravel. La ejecución final se realiza fuera de esa restricción, con dependencias originales disponibles; las trazas anteriores no se eliminaron. Laravel incluye Bootstrap 5 en assets y Bootstrap 4/jQuery en scripts: se registra esa mezcla como riesgo de integración UI para revisar por separado, sin modificar producción ni afirmar que por sí sola sea la causa comprobada.

## Defecto que continúa abierto y propuesta separada

**BUG-002 / TC-03: rechazo de apellido con ampersand.** AuthController::register admite en Apellido solo letras y espacios mediante regex. El SanitizeInput actual no reescribe el dato; la causa presente es la validación del campo. El plan original exige aceptar y conservar Sotomayor & Vargas. La implementación devuelve 422; el control equivalente sin & sí se guarda. El flujo actual crea una postulación pendiente, no una cuenta directamente; la verificación se adaptó a esa arquitectura sin cambiar reglas.

Propuesta pendiente de autorización: si TC-03 sigue vigente, ampliar de manera explícita los caracteres legítimos de Apellido y conservar el escape en las salidas. Si el requisito cambió para prohibir &, actualizar formalmente el plan/criterio de aceptación con aprobación del responsable. No cambiar silenciosamente la expectativa del test ni marcarlo PASS por el 422. No se aplicó ninguna de estas alternativas en producción.

## Rutas, middleware y selectores verificados

- Administrador: /admin/inicio; juez: /juez/dashboard; gimnasta: /gimnasta/dashboard. AuthController decide por rol. Login usa username, nacionalidadSelect, documentoInput, password y botón Autenticar ingreso.
- POST /admin/solicitudes-nivel/{id}/aprobar y /rechazar: auth.custom + permission:dashboard.admin. Controlador también comprueba permiso.
- IA API: web + auth.custom. POST /api/ia/predecir-deduccion: permission:videos.create; POST /api/ia/registrar-feedback: permission:juez.edit; GET /api/ia/estadisticas-modelo: permission:juez.view; POST /api/sepi/diagnosticar y /api/psicologia/chat: permission:areamedica.view.
- Chatbot Blade: /areamedica/psicologia/chatbot, #user-input, #send-button, #chat-messages; respuesta remota POST /api/psicologia/chat.
- Logout: form[action$="/logout"] button[type="submit"]; después de salir se exige 401 en /admin/inicio.
- BUG-008: POST /juez/store, campos ID_Gimnasta, ID_Competencia, ID_Aparato y video. Fixture con inscripción vigente, aparato Suelo y un WebM real inerte.

## Archivos modificados en este seguimiento

- tests/security/security.spec.ts: reemplaza bloqueos por integración verificable, añade controles BUG-004/006.
- utils/BackendRegression.ts: ejecuta exactamente un caso PHPUnit por BUG; adjunta stdout, JUnit y JSON; conserva fallo ante error o requisito incumplido.
- tests/backend/QaRegressionTest.php: fixtures en SQLite :memory:, almacenamiento aislado, estado antes/después, controles positivos, errores de proveedor sin red. Reutiliza patrón de tests/Feature/SecurityAccessTest.php del proyecto.
- tests/fixtures/qa-valid.webm: archivo real de video negro 16x16, sin datos personales.
- QA_DIAGNOSTICO_SEGUNDA_REVISION.md, QA_REPRODUCIR_SEGUIMIENTO.md e informe/matriz nuevos. Original anterior preservado en reports/backup-seguimiento-20261112.

En la revisión anterior se corrigieron además playwright.config.ts, LoginPage, DashboardPage, utils/qa.ts y las pruebas API/autorización/funcional/integración/regresión. Se mantienen esas correcciones y la arquitectura POM. Se ajustó además pages/DashboardPage.ts para esperar load y el plugin jQuery dropdown antes de abrir el menú. No se usó test.skip, expect.soft, force:true ni envío directo del formulario para ocultar el fallo UI.

## Trazabilidad y evidencias

Fuentes: E:\laragon\www\Proyecto2\docs\entregable1_pruebas_funcionales.md, docs/analisis_proyecto.md, routes/web.php, routes/api.php, controladores, modelos y Blade reales. Se conserva BUG-002→TC-03, BUG-004→TC-06, BUG-005→TC-07 y BUG-008→TC-11; /analisis/upload corresponde al control de TC-10, no a BUG-008. El documento original usa numeración representativa y remite al TSV para otros casos; no se inventaron enlaces TC únicos donde no se verificaron.

La ejecución final está en reports/followup/final: HTML en html/index.html, JSON en results.json y evidencias crudas en playwright/. Se conservan también chromium, cross y chromium-recheck con las ejecuciones intermedias. Los casos backend sin UI tienen JSON/JUnit/logs en lugar de capturas artificiales. Un PASS no equivale a cierre administrativo del BUG. Los resultados anteriores permanecen disponibles.

## Recomendaciones

1. Resolver el criterio de aceptación de BUG-002 antes de integrar GitHub Actions. Hay un FAIL reproducible y conocido.
2. Validar estos mismos contratos en una copia MySQL dedicada si se requiere certificar esquema completo/concurrencia; SQLite no demuestra esos aspectos.
3. Mantener los controles positivos, evidencias de estado y salida no cero ante defectos reales. No cambiar Laravel para aumentar el porcentaje.
4. No ejecutar suites simultáneas sobre las mismas credenciales: Laravel limita 5 logins por cuenta/minuto y 20 por IP/minuto. POM respeta Retry-After con un reintento.
5. Automatizar en CI después de decidir BUG-002 y acordar el alcance de aceptación; no se creó GitHub Actions.

## Verificación complementaria final

Tras ampliar el aislamiento y las comprobaciones del directorio público en BUG-008, se repitió ese caso en Chromium y Firefox: 2 PASS, con 20 aserciones PHPUnit por caso; WebKit lo ejecutó con esas comprobaciones en la suite completa. Evidencias en reports/followup/upload-final. Estos controles complementan, sin reemplazar ni ocultar, el reporte completo de 54 casos.

Propuesta de BUG-002: HALLAZGO_BUG-002.md. Reproducción: QA_REPRODUCIR_SEGUIMIENTO.md. Se verificó compilación TypeScript sin errores y coincidencia de archivos aplicados en E:\qa. El servidor local sigue configurado para gimnasia_unificada_test y no tiene config.php cacheado.
