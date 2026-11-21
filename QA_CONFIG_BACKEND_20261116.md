# Pasos 3 y 4 — configuración, datos y backend QA

Fecha: 16/11/2026. Proyecto actualizado: E:\qa. Se conservan el catálogo, las pruebas POM y los reportes anteriores, incluida la ejecución histórica de 54 PASS. No se reconstruyó la automatización ni se reejecutó toda esa suite en esta fase: las verificaciones nuevas se describen por separado.

## Paso 3 completado: configuración y datos

- Se retiraron las contraseñas operativas del código TypeScript, YAML y del ejemplo Selenium legado. Las credenciales existentes se migraron a .env.local, no se imprimieron ni se modificaron las cuentas de Laravel.
- Se añadió .env.example sin secretos y un cargador común de configuración; los valores exportados prevalecen. Las credenciales son perezosas: los casos SQLite no requieren credenciales de login que no usan. Un valor requerido ausente produce QA_CONFIG, no un PASS.
- .env.local y .runtime tienen permisos Windows para el usuario y SYSTEM. Se excluyen de control de versiones, junto con reportes y ZIP legados. El sandbox no autorizado no puede leer el archivo privado; el CLI lo señala como error de configuración.
- Se creó una cuenta MySQL restringida a gimnasia_unificada_test desde 127.0.0.1. Permisos: SELECT, INSERT, UPDATE, DELETE y CREATE TEMPORARY TABLES; sin DDL persistente. Se comprobó denegación de lectura sobre la base original. Las credenciales generadas están solo en el archivo privado.
- DatabaseHelper ya no construye comandos de shell ni permite escrituras generales. Verifica la base, ejecuta argumentos literales y no pone la contraseña en la línea de comandos.
- Se verificaron las cuatro cuentas QA: únicas, activas, con rol e ID correctos. El esquema auditado contiene 54 claves foráneas declaradas y las tablas inspeccionadas usan InnoDB.

El .env original de Laravel no se cambió. Las conexiones de la instancia QA se configuran antes del bootstrap y se verifica la base real. El router no deja ejecutar directamente PHP del directorio public para evadir la guardia: /index.php devolvió 401 sin sesión con X-QA-Database: gimnasia_unificada_test. Se rechazará una configuración Laravel cacheada hasta revisar su aislamiento.

### Diferencias medidas de motores

| Comprobación | MySQL 8.4.3 | SQLite 3.51.3 |
|---|---|---|
| abcdef en VARCHAR(2) | Rechazado, SQLSTATE 22001 | Aceptado |
| 1.239 en DECIMAL(5,2) | 1.24 | 1.239 |
| ok comparado con OK | 1 coincidencia | 0 coincidencias |
| Foreign keys en sonda | Esquema InnoDB auditado | PDO por defecto OFF; sonda ON y fixture Laravel exige PRAGMA=1 |

La auditoría no hizo migraciones ni cambios persistentes de tablas: solo metadatos, una tabla temporal MySQL y SQLite en memoria. La nueva cuenta sí modifica configuración de seguridad del servidor, sin modificar tablas de negocio originales. Estas pruebas no certifican todas las migraciones, concurrencia, bloqueos o precisión de todos los campos de la aplicación.

Los respaldos de código anterior y las evidencias históricas siguen siendo privados: no se afirma que los secretos hayan desaparecido de todas las copias antiguas. Las fuentes activas pasaron el escaneo de valores configurados sin coincidencias. Dicho escaneo no detecta todo secreto desconocido posible.

## Paso 4 completado: API de ejecución

Backend HTTP local sin dependencias adicionales, autenticado por token y sin comandos arbitrarios. Usa el catálogo real para elegir categorías completas o IDs QA-* concretos y proyectos Chromium/Firefox/WebKit. Los TC-* originales son trazabilidad, no 37 tests implementados.

Se implementaron cola serial, ID por corrida, inicio, fin, duración, progreso, resultados por caso/proyecto, historial persistente, logs redactados, cancelación en cola/en ejecución y recuperación de corridas interrumpidas. Un segundo backend de la misma instancia se rechaza antes de alterar corridas activas.

La selección se ejecuta con spawn sin shell, un worker y sin retries de Playwright. El POM mantiene el respeto de Retry-After. Los resultados deben corresponder a todos los casos/proyectos solicitados para admitir passed. Los runs incompletos y los errores globales del runner no se convierten en PASS.

| Estado de corrida | Significado |
|---|---|
| queued / running | En cola / en ejecución |
| passed | Terminó sin fallos; mantiene SKIPPED visibles si los hubiera |
| tests_failed | Aserción/comportamiento esperado incumplido; no prueba por sí solo defecto de aplicación |
| infrastructure_error | Configuración, conexión, instalación, proceso o resultados incompletos |
| cancelled / interrupted | Cancelada / recuperada tras interrupción, sin reejecución automática |

Por caso: PASS, FAIL, SKIPPED o NOT_RUN, con failureKind test/infrastructure/runner cuando corresponde. Un locator que agota tiempo sin evidencia de infraestructura sigue siendo FAIL de prueba. Los bloqueos explícitos de configuración se registran NOT_RUN con su causa.

Rutas: GET /api/health, GET /api/catalog, POST /api/runs, GET /api/runs, GET /api/runs/{id}, GET /api/runs/{id}/log y POST /api/runs/{id}/cancel. Todas requieren Authorization Bearer. La API rechaza origen cruzado, campos desconocidos, IDs no implementados, mezclas incompatibles y solicitudes excesivas.

## Validación nueva

| Verificación | Resultado |
|---|---|
| Pruebas de backend/configuración (algunas con dobles de proceso) | 12 PASS, 0 FAIL |
| Selección real de tres casos | 3 PASS |
| Categoría completa security / nonfunctional, Chromium | 9 PASS |
| Trabajo real de prueba durante comprobación de cola | 1 PASS |
| Cancelación en cola y ejecución | Registradas cancelled; no ejecutados visibles |
| Aserción fallida controlada con fixture de contraseña incorrecta | tests_failed, 1 FAIL; no se considera nuevo defecto de Laravel |
| Aplicación detenida de forma controlada | infrastructure_error, NOT_RUN; no FAIL funcional |
| Versión instalada en E:\qa con cuenta MySQL restringida | 3 PASS: login admin, BUG-002 y recurso protegido |
| Falta de aplicación en versión instalada | infrastructure_error, NOT_RUN=1, infrastructureCases=1 |
| TypeScript y catálogo | Compilación e integridad correctas |

Las corridas controladas de fallo/cancelación están etiquetadas en VALIDACION.json y se conservaron. No se suman como defectos nuevos ni sustituyen la suite histórica de 54 PASS. La categoría se comprobó antes de restringir la cuenta y la instalación final se comprobó de nuevo con la cuenta restringida. Un contador de infraestructura de la primera comprobación se ajustó; la corrida final instalada confirma infrastructureCases=1 sin modificar el historial anterior.

Corrida final instalada: qa-2026-11-16T17-25-53-282Z-be402968e4d3; duración 20122 ms. Corrida final de infraestructura: qa-2026-11-16T17-25-20-918Z-b0407593ec6a; causa fetch failed (ECONNREFUSED).

## Uso

1. Iniciar MySQL de Laragon.
2. En E:\qa: npm run qa:app.
3. En otra consola: npm run qa:backend.
4. npm run qa:run -- case QA-SEC-002,QA-SEC-005,QA-SEC-008 chromium
5. npm run qa:run -- category security chromium

Para casos solo SQLite no se necesita MySQL/HTTP. Se entrega .env.example, no el archivo privado ni su token. Los servicios temporales de validación se cerraron; el backend está implementado y listo para iniciarse con esos comandos. La configuración privada de esta instalación ya está preparada.

Los reportes nuevos están en E:\qa\reports\runs y la evidencia de esta fase en reports/fase34-20261116-132017. Los respaldos de fuentes anteriores están en .runtime/backups (privado). No se borraron datos, reportes ni archivos previos.

## Siguiente etapa

Conectar la interfaz web al contrato de esta API. Los generadores de Word/PDF, HTML de entrega y correo pertenecen a las fases siguientes. El catálogo mantiene 37 casos: 17 con cobertura completa, 2 parcial y 18 pendientes; no cambió esa cobertura por crear el backend.
