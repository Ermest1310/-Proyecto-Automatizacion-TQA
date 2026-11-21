# Plataforma integral QA

Evolución local del ejecutor existente. No modifica Laravel, reglas de negocio ni la base original. Los resultados históricos conservan su procedencia y no equivalen a una validación nueva.

## Historial y evidencias

- `GET /api/platform/history`: últimas 100 ejecuciones; separa archivos no verificables.
- `GET /api/platform/compare?base=ID&target=ID`: compara caso y proyecto; clasifica regresión, recuperación, altas/bajas y cambios de configuración/definición.
- `GET /api/platform/flakiness`: candidatos con al menos tres observaciones y dos alternancias PASS/FAIL. Agrupa versiones, entorno y proyecto. No incluye demostraciones por defecto ni confunde NOT_RUN con FAIL.
- `GET /api/platform/timeline?caseId=ID`: trayectoria observada y ejecuciones que no pudieron verificarse.
- `GET /api/platform/runs/ID/archive`, `/integrity`, `/evidence?caseId=ID&project=PROYECTO`.

Requieren la autenticación existente. Las mutaciones posteriores conservan CSRF para sesiones web. Los originales permanecen privados. El centro muestra tipos, tamaños, SHA-256 y extractos JSON con campos permitidos; no entrega capturas, trazas ni cuerpos HTTP brutos. Los pasos instrumentados publican clases de operación, no valores introducidos.

Cada archivo `platform/archive-v2.json` tiene huella SHA-256 del modelo depurado, de los resultados fuente y de los archivos de evidencia disponibles. Los archivos de versión anterior se conservan. Una alteración de fuente bloquea el uso del archivo; una alteración del archivo se detecta al verificarlo. No es una firma electrónica ni protege frente a alguien con control total del disco que reescriba simultáneamente fuentes y huellas.

La política `qa-redaction-1` elimina secretos configurados, credenciales reconocibles, JWT, correos, rutas y campos personales conocidos. Las estructuras desconocidas de evidencia se omiten. La redacción automática no garantiza anonimización universal de texto libre; por eso no se exportan binarios ni valores libres de formularios. Los originales deben conservarse en un equipo protegido con acceso restringido.

Las nuevas ejecuciones capturan huellas de código QA y lockfile. Las antiguas sin snapshot se identifican como procedencia desconocida; nunca se les asigna retrospectivamente la versión actual. Una diferencia temporal entre dos ejecuciones es una observación, no una conclusión de rendimiento: se necesitan muestras y condiciones comparables.

## Límites conservados

- TC-34 continúa parcialmente validado y bloqueado por falta de vídeo autorizado y resultado de referencia.
- La demostración PASS/FAIL valida el circuito QA, no demuestra defectos ni correcciones en Laravel.
- La indisponibilidad de MySQL se presenta como infraestructura; SQLite no sustituye la validación del motor real.
- No se envía correo externo ni se publica el servicio. El buzón de demostración solo recibe localmente.

## Diagnóstico operativo del historial

- `401`: falta sesión válida; no se entregan archivos.
- `403`: origen, CSRF o ruta/enlace fuera del proyecto rechazado.
- `404`: ejecución o caso inexistente.
- `409`: ejecución aún activa o integridad de fuente incumplida; no convertirlo en PASS.
- `413`: límites de evidencia, profundidad o modelo excedidos. Revisar el caso; no ampliar límites automáticamente.
- `422`: parámetros ambiguos o selección inválida.
- `500`: respuesta con referencia opaca; detalle depurado en `reports/platform/system-errors.ndjson`.

La comparación propone ejecuciones del mismo alcance cuando existen. Un caso fuera de la selección no se interpreta como eliminado del producto. Las tablas permiten desplazamiento y foco por teclado. Al cerrar sesión se limpian los datos de los paneles.

Validación inicial del Bloque 1: recorrido de interfaz aprobado en Chromium, Firefox y WebKit; comprobaciones de autenticación, comparación, integridad, evidencias, trayectoria, móvil de 390 px y cierre de sesión. Prueba sostenida de lectura: 355 solicitudes, 0 errores; p95 observado 452 ms. Es una medición local de QA, no un benchmark ni una garantía de rendimiento de Laravel.

## Catálogo, requisitos y suites

La interfaz incorpora formularios con campos de objetivo, precondiciones, datos, pasos ordenados y criterios. Los selectores permiten elegir casos, requisitos, dependencias y etiquetas existentes. Crear un caso no genera una implementación ejecutable: se muestra como pendiente. Cambiar pasos, precondiciones, datos o resultado esperado de una prueba vinculada la retira del alcance ejecutable hasta sincronizar el código del catálogo original; no se alteran aserciones para conseguir PASS.

Los 37 criterios originales se importan como requisitos derivados del plan (`REQ-TC-01`…`REQ-TC-37`), conservando TC e identificadores BUG. No sustituyen el documento fuente. Los nuevos requisitos pueden vincularse a casos existentes, pero los vínculos originales no pueden eliminarse. Cada selección de ejecución guarda la revisión y criterios de sus requisitos.

La persistencia está en `reports/platform/entities`: revisiones inmutables, hash encadenado, autor declarado, fecha, motivo y diff. El anclaje detecta truncamiento del historial cuando está disponible; los registros iniciales anteriores a su incorporación se verifican y anclan mediante una migración con fecha actual, sin inventar un anclaje histórico. Después de migrar, un anclaje desaparecido no se reconstruye automáticamente. Un conflicto de versión devuelve 409 y no sobreescribe la edición vigente. La restauración genera una revisión nueva; el archivado es lógico. Una escritura interrumpida o una integridad incumplida requiere revisar los archivos antes de editar, sin reescribir silenciosamente el historial.

APIs autenticadas:

- `GET /api/platform/catalog` y `/traceability`.
- `GET|POST /api/platform/entities/{cases|requirements|suites}`.
- `GET|PUT|DELETE /api/platform/entities/COLECCIÓN/ID`.
- `GET .../ID/history` y `POST .../ID/restore`.
- Mutaciones: `{data, expectedVersion, author, reason}`; archivado sin `data`; restauración con `version`.
- `GET /api/platform/suites/ID/plan`, `POST .../execute` con `{recipient}`.
- `GET /api/platform/suite-jobs/ID` y `POST .../cancel` con `{}`.

Los filtros de una suite se combinan por intersección. Las dependencias se ordenan antes de sus dependientes y pueden incorporarse aunque no coincidan con los filtros. Cuando hay dependencias se crean ejecuciones individuales. Un previo no aprobado produce NOT_RUN en el dependiente, sin iniciarlo. No se transfieren sesiones ni datos implícitamente entre casos. Configuraciones HTTP, navegador, MySQL suplementaria y demo se ejecutan en grupos compatibles. La cancelación solo afecta a los hijos de esa suite.

## Hallazgos

Un FAIL con evidencia verificada puede originar un hallazgo. NOT_RUN e infraestructura no demuestran un defecto funcional. Los hallazgos de demostración se identifican y no representan defectos del producto. Se conservan el caso, BUG originales, proyecto, ejecución, definición y huellas de evidencia.

Estados: `open → triaged → fixed → verified`, con transiciones de reapertura y posibilidad de documentar la corrección desde open. `fixed` exige resolución. `verified` exige un PASS posterior a la corrección declarada, del mismo caso, proyecto y definición, sin cobertura bloqueada y con integridad válida. Un cambio de aserción no verifica automáticamente el defecto.

- `POST /api/platform/defects/from-failure`: `{failure:{runId,caseId,project,title?,description?,severity?},author,reason}`.
- `PATCH /api/platform/defects/ID`: `{data,expectedVersion,author,reason}`.
- Consulta e historial mediante la colección `defects`.

El recorrido de interfaz de este bloque se comprobó en los tres motores. La prueba de dependencia real produjo 1 FAIL intencional y un dependiente NOT_RUN sin PID de runner; posteriormente se restauraron los metadatos demo mediante otra revisión.

El comando `node scripts/verify-platform.cjs` conserva un informe de verificación sin recuperar procesos ni modificar resultados fuente. Los metadatos preexistentes verificados se migran a anclajes con fecha actual; una pérdida posterior del anclaje o una revisión alterada se bloquea para revisión. Los autores son atribuciones declaradas bajo un acceso local compartido.

Validación de cierre del Bloque 2: 83 pruebas de backend aprobadas, tipado TypeScript correcto, catálogo original válido y búsqueda de secretos sin coincidencias. Las pruebas de UI incluyen creación, revisión, restauración, archivado, selector visual, seguimiento, recepción local, cancelación y defensa frente a marcado HTML almacenado. Las entidades sintéticas creadas en el ensayo no forman parte de la cobertura del producto.

## Datos sintéticos y limpieza por ejecución

Cada nueva ejecución orquestada obtiene semilla uint32, `execution_id`, `fixtures/data.json` y manifiesto con SHA-256. El generador `synthetic-v1` produce valores controlados; los correos incorporan un namespace derivado del ID para impedir que una reproducción adopte registros de otra corrida. No genera contraseñas. El manifiesto de archivos usa hashes de integridad; el registro SQL, adicionalmente, usa HMAC con la clave privada local.

La demostración consume y valida el manifiesto real. La prueba suplementaria de registro MySQL utiliza los datos registrados, reclama identidades ausentes antes de escribir y captura IDs y huellas de filas creadas. Su cierre intenta limpiar sus propias postulaciones; si ya hubo un fallo funcional, un problema adicional de limpieza se adjunta sin reemplazar el fallo original. No se habilita SQL genérico ni se modifica el esquema Laravel.

El adaptador `scripts/mysql-owned-fixtures.php` solo admite `gimnasia_unificada_test` en loopback y la tabla `postulaciones`. Verifica ejecución, namespace, firma, identidad del servidor, ausencia previa y huellas exactas. Una fila modificada o referenciada bloquea la transacción completa. No usa TRUNCATE, migraciones ni DELETE por prefijos. Las filas ajenas permanecen intactas. La limpieza de archivos solo elimina `data.json` registrado; preserva manifiesto, evidencias, resultados, informes y correo. Una intención íntegra permite recuperar una interrupción entre borrado y recibo.

- `GET /api/platform/fixtures/ID`: vista previa de propiedad y estado.
- `POST /api/platform/fixtures/ID/cleanup` con `{}`: solo ejecución terminal.
- Los SQLite de PHPUnit son bases en memoria independientes que desaparecen al finalizar, no copias intercambiables de MySQL.

Validación SQL: MySQL 8 real en un servidor efímero con directorio nuevo y puerto 3337; 22 aserciones aprobadas, incluida preservación de una fila centinela ajena, rollback, referencias, firma, identidades preexistentes y rechazo de base/servidor incorrectos. Ese servidor se detuvo al acabar. La instancia original de Laragon no se inició. Un intento intermedio de inicialización agotó 90 s antes de ejecutar pruebas; quedó como infraestructura y se revalidó con límite de 180 s.

## Snapshot y reproducción

Cada nueva ejecución conserva huellas del código QA, código Laravel pertinente cuando aplica, lockfiles, versiones Node/Playwright/PHP, configuración depurada, semilla, valores base y revisiones de requisitos. No conserva secretos, contenido de bases ni código descargado de servicios externos.

- `GET /api/platform/snapshots/ID`: snapshot, integridad, límites y condiciones de reproducción.
- `POST /api/platform/snapshots/ID/replay` con `{recipient}`: nueva ejecución vinculada al origen.
- Las ejecuciones históricas sin snapshot no reciben retrospectivamente una versión supuesta.

Se rechazan cambios de código, definición, configuración relevante, versiones, datos alterados o recursos SQL pendientes. Las dependencias requieren reproducir la suite completa. El backend también rechaza nuevas corridas si el código QA cambia después de su arranque: reiniciar permite cargar una versión coherente. No se restaura código antiguo sobre el proyecto actual.

La semilla reproduce el generador sintético; los IDs y correos cambian de namespace. Los salts aleatorios internos de PHPUnit, credenciales vigentes, reloj del navegador, MySQL y proveedores externos no se congelan. La acción se ofrece únicamente cuando las condiciones verificables coinciden, sin prometer igualdad byte a byte de todo el entorno.

## Orquestación y CI

`GET|POST /api/platform/orchestration` consulta la cola o configura `{maxWorkers:1|2}` para el proceso actual. Por defecto se mantiene un worker (`QA_MAX_WORKERS=1`). Dos workers solo solapan ejecuciones aisladas de demo/SQLite; aplicación y perfiles HTTP comparten un recurso exclusivo. Cada hijo tiene entorno, directorios, archivos y worker ID propios. La cancelación termina únicamente su árbol de procesos. La prioridad 0–10 admite envejecimiento de cola y nunca omite dependencias. Tras interrupción se conserva el evento/estado de recuperación sin reejecución automática.

`POST /api/platform/priority/ID` recibe `{priority}` solo para queued.

`npm run qa:configure` agrega un secreto CI si no existe, sin imprimirlo ni cambiar valores privados previos. El webhook `POST /api/ci/webhook` usa:

- `X-QA-Timestamp`: segundos Unix, tolerancia 300 s.
- `X-QA-Delivery`: ID único ASCII.
- `X-QA-Signature`: `sha256=` seguido de HMAC-SHA256 de `timestamp.delivery.rawBody` con `QA_CI_WEBHOOK_SECRET`.
- JSON: `{caseIds, projects?, recipient, priority?}` o `{suiteId, recipient, priority?}`.

No acepta comandos, entornos ni rutas. La reserva durable evita reejecutar entregas duplicadas o inciertas. `GET /api/platform/ci` muestra estado y entregas sin revelar secretos. El servidor sigue en loopback: un CI remoto necesita un runner local o una configuración de acceso expresamente administrada; no se publicó GitHub Actions ni un endpoint público.

## Mutación y preparación de entrega

`POST /api/platform/mutations` con `{}` crea una ejecución aislada; GET consulta la última. Las cinco alteraciones se aplican en copias bajo reports, con comprobación de sintaxis y suite base. Un fallo de infraestructura o un mutante inválido no cuenta como detección. Los SHA originales se verifican al finalizar y se contrastan al consultar el resultado. El alcance son controles críticos de la plataforma QA, no cobertura mutacional completa de Laravel.

La primera ronda dejó sobrevivir un cambio neutralizado por otra defensa. Se conservó esa evidencia y se sustituyó por una alteración efectiva de aceptación de fixtures modificados. La ronda válida detectó 5/5 alteraciones sin cambiar originales.

`POST /api/platform/readiness` con `{}` evalúa; GET consulta la última evaluación. La política versionada está en `config/readiness-policy.json`. Cada decisión conserva reglas, referencias, entrada, fecha y SHA: `LISTA`, `BLOQUEADA` o `REQUIERE REVISIÓN`. Faltas de cobertura, resultados ausentes/fallidos, infraestructura y defectos altos bloquean; muestras insuficientes o defectos menores requieren revisión. Ninguna ausencia se transforma en PASS. La evaluación actual queda BLOQUEADA por TC-34 y falta de evidencias vigentes/entorno vivo; no confundir las demostraciones con validación nueva del producto.

## Diagnóstico y proveedor IA opcional

`POST /api/platform/assistant/ID` con `{external:false}` genera observaciones e hipótesis locales, citando ejecución, caso, integridad y evidencias. No cierra defectos ni modifica pruebas. Un proveedor externo solo se llama si se solicita expresamente `{external:true}` y están configurados `QA_AI_ENABLED=true`, `QA_AI_API_KEY` y `QA_AI_MODEL` en el entorno privado. No se reutilizan secretos de Laravel. Sin configuración o ante fallo del proveedor permanece el análisis local, identificado como reglas y no como IA generativa.

El adaptador utiliza la [API Responses oficial](https://developers.openai.com/api/reference/resources/responses/methods/create), contrastada con [OpenAI Docs](https://developers.openai.com/api/docs/quickstart). Envía únicamente IDs, estados, métricas numéricas y hashes, sin imágenes ni formularios, con salida acotada y almacenamiento remoto desactivado en la solicitud. La conexión se verificó con un transporte simulado; no se realizó una llamada pagada real porque no hay un proveedor configurado para QA.

## Informes y entrega por versión

`POST /api/runs/ID/reports` admite `{profile:"executive"|"technical",deliver:true|false}`. Cada versión conserva PDF, HTML autónomo, modelo y SHA. El perfil ejecutivo resume resultados y trazabilidad; el técnico añade pasos, requisitos versionados y evidencias. No se reemplazan versiones anteriores. Los archivos descargados también se contrastan con su SHA.

`GET|POST /api/platform/deliveries/ID/VERSIÓN` consulta o solicita entrega de esa versión. La deduplicación se mantiene por versión y se reutilizan las comprobaciones SMTP, integridad y recepción local existentes. La entrega automática técnica original conserva su comportamiento. Un envío incierto no se reintenta automáticamente.

## Arranque y comprobación en Windows

```powershell
Set-Location E:\qa
npm run qa:configure
npm run qa:start
npm run qa:status
```

Abrir `http://127.0.0.1:9876` y usar el token privado ya configurado. El modo aislado funciona sin iniciar MySQL; para pruebas HTTP/MySQL, iniciar el servicio correspondiente en Laragon y reiniciar el conjunto QA. No se migra ni limpia la base original.

```powershell
npm run qa:test-backend
npx tsc --noEmit
npm run qa:catalog
npm run qa:verify
npm run qa:secrets-check
npm run qa:stop
```

La prueba explícita `npm run qa:test-mysql-fixtures` inicia y detiene su propio MySQL efímero en puerto 3337, sin usar el directorio original. Conserva sus artefactos para auditoría. No se incluye en la ejecución rápida habitual.
