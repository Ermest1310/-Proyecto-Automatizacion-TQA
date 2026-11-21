# Backend local de ejecución QA

El backend reutiliza el catálogo y los tests existentes. No implementa todavía la interfaz web, el correo ni los generadores de documentos de fases posteriores. No acepta comandos, rutas, scripts o variables de entorno enviados por el cliente.

Arranque: `npm run qa:backend` (127.0.0.1:9876 por defecto). Cada petición requiere `Authorization: Bearer <QA_API_TOKEN>`; el CLI carga el token privado sin imprimirlo. Solo se permite una instancia por carpeta/estado; un segundo arranque no puede recuperar indebidamente corridas de un proceso activo.

| Método / ruta | Función |
|---|---|
| GET /api/health | Salud del backend |
| GET /api/catalog | Catálogo, categorías, pruebas implementadas y trazabilidad |
| POST /api/runs | Crear corrida y encolarla (202) |
| GET /api/runs | Hasta 100 corridas recientes |
| GET /api/runs/{id} | Estado, selección, tiempos, progreso y resultados |
| GET /api/runs/{id}/log | Log redactado y acotado |
| POST /api/runs/{id}/cancel | Cancelar un trabajo en cola o su árbol de procesos propio |

Selección por casos:

```json
{"caseIds":["QA-SEC-002","QA-SEC-005"],"projects":["chromium"]}
```

Selección por categoría completa:

```json
{"category":"security","type":"nonfunctional","projects":["chromium"]}
```

`category: all` selecciona los 18 tests habituales. Los IDs QA-* identifican pruebas ejecutables; los 37 TC-* son casos de trazabilidad, algunos parciales o pendientes, por lo que no se ejecutan como si todos estuvieran implementados. `projects` admite Chromium/Firefox/WebKit según configuración. No se mezclan la configuración habitual y la validación suplementaria de registro; esta última es explícita y solo Chromium.

CLI sin contraseñas ni token en argumentos:

```text
npm run qa:run -- case QA-SEC-002,QA-SEC-005,QA-SEC-008 chromium
npm run qa:run -- category security chromium
```

La cola es serial, con un worker y sin retries de Playwright, para respetar cuentas compartidas. Se conserva el Retry-After del login en el POM. Un run tiene ID único, createdAt, startedAt, finishedAt, durationMs, selección y resultados por caso/proyecto. La espera en cola no se incluye en durationMs de ejecución.

## Estados y errores

- queued / running: pendiente / en curso.
- passed: corrida terminada sin fallos registrados; el resumen mantiene explícitos los SKIPPED si existen.
- tests_failed: falló una aserción o comportamiento esperado. No significa automáticamente defecto de aplicación: puede ser un dato de prueba incorrecto y se debe revisar su evidencia.
- infrastructure_error: configuración, precondición, conexión, instalación, timeout del proceso, resultados incompletos o error global del runner. Los casos no ejecutados se registran NOT_RUN, no PASS ni FAIL funcional.
- cancelled: cancelación solicitada, con los resultados ya obtenidos y NOT_RUN para el resto.
- interrupted: detectado al recuperar una corrida no terminal tras interrupción del proceso. No se reejecuta automáticamente.

Por caso: PASS, FAIL, SKIPPED o NOT_RUN, con failureKind test / infrastructure / runner cuando corresponda. Los bloqueos explícitos del framework se muestran NOT_RUN con causa de infraestructura; no se ocultan mediante skip. Un timeout de un locator sin evidencia de infraestructura sigue siendo FAIL de test, no se reclasifica automáticamente como bloqueo.

Exit del CLI: 0 para passed, 1 para tests_failed, 2 para infraestructura/cancelación/interrupción. Los IDs, proyectos, archivos y títulos se validan contra el catálogo. La ejecución usa spawn con shell:false. Los resultados deben coincidir con todos los casos/proyectos seleccionados para admitir passed.

## Persistencia

Cada corrida conserva selection.json, run.json, results.json, events.ndjson y runner.log bajo reports/runs/{id}. Los eventos se agregan; los manifiestos se escriben de forma atómica. Evidencias internas: evidence/. No se reemplazan reportes históricos. Al reiniciar, los runs no terminales se marcan interrupted; el operador debe revisar posibles procesos huérfanos tras una terminación abrupta, sin reusar PID a ciegas.

`QA_BACKEND_INSTANCE` es una opción local avanzada para verificaciones aisladas: estado bajo reports/instances/{nombre}/runs y bloqueo propio. No es un campo aceptado por la API. No usar varias instancias concurrentes con las mismas cuentas; la operación normal utiliza una sola instancia.

## Comprobación

`npm run qa:test-backend` ejecuta pruebas de selección, autorización HTTP, validación, clasificación, cola, persistencia, cancelación, recuperación, bloqueo de segunda instancia y seguridad del helper SQL. Algunos tests usan dobles de proceso para aislar la lógica; no son ejecuciones de la aplicación Laravel. Las corridas reales del backend y los fallos controlados tienen evidencias separadas.


## Ampliación de interfaz y perfiles HTTP — 16/11/2026

`recipient` es un campo opcional de selección para clientes Bearer y obligatorio para la interfaz con sesión. Se valida en el servidor. Nunca significa que se haya enviado un mensaje.

Las categorías `performance` y `load` utilizan proyecto `http`, inferido automáticamente si se omite `projects`; los demás casos utilizan Chromium por defecto. No mezclar configuraciones. `category: all` requiere acotar `type: functional` para la suite funcional común; seleccionar todas las categorías sin tipo ahora abarca configuraciones incompatibles y se rechaza. Para no funcionales, ejecutar cada categoría o indicar IDs compatibles. La validación MySQL suplementaria sigue separada y explícita.

Las métricas HTTP se incorporan al resultado de cada caso. Los estados existentes y las carpetas históricas mantienen su significado. La sesión de la web y sus controles se documentan en INTERFAZ_WEB.md.
