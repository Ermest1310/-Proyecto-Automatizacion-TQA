# Informe de implementación: interfaz web y pruebas no funcionales

Fecha: 16/11/2026. Proyecto existente `E:\qa`. Se amplió el backend previo; se conservaron POM, pruebas originales, configuración privada, base original e informes anteriores. No se modificó Laravel ni se enviaron correos.

## Entregado

- Interfaz local responsive con correo validado, tipo/categoría, categoría completa o casos concretos, navegadores compatibles y proyecto HTTP automático.
- Sesión local, botón de ejecución, cancelación, progreso de casos, resultados, errores, métricas y consulta del historial persistido.
- Tres perfiles de lectura: latencia base, carga ligera y estabilidad corta, con límites de código y criterios documentados.
- Muestras y métricas enlazadas al resultado de cada caso; la ausencia de evidencia no puede convertirse en PASS de rendimiento.
- Catálogo: 18 casos comunes + 1 suplementario + 3 HTTP = 22 ejecutables. Se conservan los 37 TC originales y su cobertura: 17 completos, 2 parciales, 18 pendientes.

## Evidencia integrada real

| Ejecución | Alcance | PASS | FAIL | NOT RUN | SKIPPED |
|---|---|---:|---:|---:|---:|
| qa-2026-11-16T18-07-44-711Z-608547094912 | Categoría performance, HTTP | 1 | 1 | 0 | 0 |
| qa-2026-11-16T18-08-44-242Z-fc7fef6f13b1 | Categoría load, HTTP | 1 | 0 | 0 | 0 |
| qa-2026-11-16T18-09-54-071Z-8c2939e5b58c | Caso QA-SEC-002 seleccionado desde web | 1 | 0 | 0 | 0 |

Total de estos casos reales: 3 PASS, 1 FAIL, 0 NOT RUN, 0 SKIPPED. Los tres perfiles HTTP por sí solos tienen 2 PASS y 1 FAIL. No es una nueva ejecución de los 54 resultados históricos de navegador.

Las 64 solicitudes medidas a /login fueron HTTP 200 y cumplieron el contenido real del formulario. QA-PERF-001: p95 822,8 ms / 1500 ms; QA-LOAD-001: 555,8 ms / 2500 ms; QA-PERF-002: 2880,4 ms / 2000 ms, FAIL conservado. En estabilidad, la degradación de mediana sí cumplió. Presupuestos, límites, interpretación y limitaciones están en RENDIMIENTO_CARGA.md.

Se verificaron acceso sin sesión, sesión mediante cookie HttpOnly, correo inválido rechazado, ejecución por categoría y por selección, visualización de un FAIL real, progreso, métricas, historial tras recargar, ausencia del token en DOM/almacenamiento, ausencia de errores JavaScript y móvil de 390 px sin desbordamiento horizontal. Capturas y JSON quedan en `reports/web-validation-2026-11-16T18-09-48-673Z/`. Los primeros intentos de validación permanecen como historial; el script se ajustó para verificar también estados FAIL legítimos y utilizar el tipo/categoría reales de BUG-002 (funcional/regression).

Comprobaciones de soporte: 18/18 pruebas Node del ejecutor, sesión, selección, límites, métricas y seguridad de evidencia; catálogo validado; TypeScript sin errores; escaneo de valores privados sin hallazgos. Las pruebas de límites emplean servidores efímeros controlados y se distinguen de las mediciones reales anteriores.

## Archivos

- `web/index.html`, `web/app.css`, `web/app.js`: interfaz.
- `backend/web.cjs`, `backend/email.cjs`, `backend/server.cjs`: sesión, protección de escrituras, correo y recursos públicos limitados.
- `backend/selection.cjs`, `backend/manager.cjs`, `backend/cli.cjs`: selección HTTP, destinatario, historial y precondiciones según el tipo real.
- `backend/playwright-reporter.cjs`: métricas y rechazo de aprobación sin evidencia.
- `config/performance-profiles.json`, `config/test-catalog.json`: perfiles y catálogo ampliado.
- `utils/performance.cjs`, `tests/performance/performance.spec.ts`, `playwright.performance.config.ts`: carga limitada, aserciones y evidencias.
- `playwright.config.ts`: exclusión de perfiles de la suite histórica de 18 casos por navegador.
- `backend/tests/backend.test.cjs`, `backend/tests/web-performance.test.cjs`, `scripts/validate-catalog.cjs`, `scripts/validate-web.cjs`: verificaciones.
- `package.json`, `README.md`, `docs/BACKEND_EJECUCION.md` y las tres guías/informe de esta fase.

No se añadieron dependencias. Los archivos anteriores que se reemplazaron tienen respaldo fechado en `.runtime/backups/fase56-*`. La configuración privada no se incluye en los entregables.

## Verificación de la instalación en E:\qa

Ejecución adicional `qa-2026-11-16T18-15-17-325Z-c5476363ae15`: QA-PERF-001 seleccionado desde la interfaz instalada, PASS, 10/10 respuestas válidas, p95 683,8 ms / 1500 ms. Evidencia en `reports/web-installed-1794852915863/`. Este control adicional no reemplaza el FAIL de estabilidad anterior. Total de casos reales nuevos de esta fase, incluyendo este control: 4 PASS, 1 FAIL, 0 NOT RUN, 0 SKIPPED; las 18 pruebas Node son comprobaciones separadas.

## Pendiente

1. Investigar el pico de latencia de estabilidad con condiciones de ejecución controladas, sin elevar el umbral para aprobarlo.
2. Generadores finales de informes HTML y Word/PDF y su descarga desde la UI.
3. Integración del envío automático: el destinatario ya se valida y persiste, pero todavía no existe entrega de correo.
4. Completar los casos pendientes del plan original y las siguientes fases del proyecto integrador.

El arranque y la operación están en INTERFAZ_WEB.md. Los servicios temporales de validación se detienen al finalizar; el código instalado queda listo para iniciarse con MySQL, `npm run qa:app` y `npm run qa:backend`.
