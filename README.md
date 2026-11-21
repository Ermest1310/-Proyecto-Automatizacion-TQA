# QA Automation — Proyecto2 (Laravel)

## Arranque y cobertura vigentes

```powershell
cd E:\qa
npm run qa:start
```

Abrir http://127.0.0.1:9876/. `qa:doctor`, `qa:status` y `qa:stop` permiten comprobar y cerrar solo los servicios del conjunto. MySQL sigue administrado por Laragon; con él apagado funcionan las pruebas aisladas y la web indica que HTTP está bloqueado.

La matriz original conserva 36/37 casos completos; TC-34 mantiene cobertura parcial porque falta un vídeo/oráculo autorizado para validar MediaPipe. La expansión suma 127 escenarios: 23 casos modulares iniciales y 104 casos profundos, incluidos los 60 agregados en esta ronda para sesiones, evaluaciones, rehabilitación, video, FIG, sistema experto, roles y ML. Hay 171 ejecutables en total; 165 pertenecen a suites funcionales/de integración/seguridad y el resto son perfiles HTTP, una validación MySQL y demostraciones. [Ampliación del sistema](docs/AMPLIACION_COBERTURA_SISTEMA.md), [guía de cobertura](docs/ARRANQUE_Y_COBERTURA.md) y [matriz original](docs/COBERTURA_37_VIGENTE.md).

Los recuentos de las fases anteriores que siguen debajo se conservan como historial; las cifras vigentes están arriba y en el catálogo canónico.


## Correo automático y demostración integral - 16/11/2026

Los puntos 9 y 10 están integrados. PDF y HTML se envían al finalizar, con estado independiente de entrega, registro de errores y reintentos limitados. La recepción acordada se verifica en un buzón SMTP local, sin correo externo. [Guía y demostración](docs/CORREO_Y_DEMOSTRACION.md).

- `npm run qa:inbox`: buzón local; luego `npm run qa:backend` y abrir la web.
- Destinatario de demostración: `demo@localhost.test`.
- Categoría «Demostración controlada»: 1 PASS y 1 FAIL intencional, separados de las pruebas de Laravel.
- El catálogo tiene 24 ejecutables y conserva los 37 TC originales. La suite de aplicación sigue en 18 casos por navegador.
- Las secciones siguientes conservan el estado y contexto de las fases anteriores; el envío ya está implementado en modo local y SMTP externo configurable.

## PDF y HTML independiente - 16/11/2026

Los puntos 7 y 8 ya están integrados: generación automática al finalizar, descarga desde la interfaz y exportación de ejecuciones históricas sin reejecutarlas. [Guía de informes](docs/INFORMES_EXPORTABLES.md).

- PDF A4 con resumen, pasos, resultados, métricas y evidencias depuradas por caso.
- HTML de un único archivo que abre sin conexión, con tabla, observaciones y totales.
- `npm run qa:export -- qa-ID-DE-EJECUCION`: exportación por CLI.
- El envío automático de correo sigue pendiente.

## Interfaz y pruebas no funcionales vigentes — 16/11/2026

- `npm run qa:backend` sirve la interfaz y API en http://127.0.0.1:9876/.
- [Uso de la interfaz](docs/INTERFAZ_WEB.md): correo, filtros, selección, progreso, resultados e historial.
- [Perfiles y criterios de carga](docs/RENDIMIENTO_CARGA.md): tres pruebas HTTP limitadas, separadas de los 18 casos por navegador.
- `npm run qa:performance` ejecuta los tres perfiles y conserva una carpeta nueva.
- El correo se valida y registra; el envío automático sigue pendiente.
- Primera medición: 2 PASS y 1 FAIL de latencia, conservado sin cambiar criterios. Detalles en `docs/INFORME_FASES_5_6.md`.

## Configuración y backend vigentes — 16/11/2026

La configuración operativa está en `.env.local` privado; no copiar `.env.example` sobre el archivo ya migrado. No hay contraseñas de cuentas QA en el código activo. Los reportes y respaldos históricos permanecen privados y no se borraron.

- Guía de configuración y arranque: `docs/CONFIGURACION_SEGURA.md`.
- API y estados de ejecución: `docs/BACKEND_EJECUCION.md`.
- Diferencias verificadas MySQL/SQLite: `docs/MYSQL_SQLITE.md`.
- `npm run qa:backend`: API local autenticada.
- `npm run qa:app`: instancia Laravel de QA; requiere MySQL iniciado.
- `npm run qa:run -- category security chromium`: categoría completa.
- `npm run qa:run -- case QA-SEC-002,QA-SEC-005 chromium`: casos seleccionados.
- `npm run qa:test-backend`, `npm run qa:catalog`, `npm run qa:secrets-check`: verificaciones del sistema.

La instancia QA limita la conexión a `gimnasia_unificada_test` y el usuario MySQL del runner no tiene acceso a la base original. Los casos solo SQLite pueden ejecutarse sin la instancia HTTP. La interfaz web ya está implementada; los generadores y el envío de entregables corresponden a fases posteriores.

Las notas y estados de fases anteriores que siguen abajo se conservan como historial; la documentación vigente es la indicada arriba.

Sistema de automatización de pruebas y control de calidad (QA) para el proyecto evaluado.

## Stack

| Componente | Tecnología |
|---|---|
| Automatización web | Playwright + TypeScript |
| Patrón de diseño | Page Object Model (POM) |
| Backend / reglas de negocio | PHPUnit / Laravel Testing |
| API testing | Playwright (`request` fixture) |
| Base de datos | MySQL de pruebas |
| Reportes | Playwright HTML Report + evidencias |

## Estructura del proyecto

```
E:\qa
├── config/
│   ├── environments.ts   # URLs y configuración por entorno
│   └── test-data.ts       # Usuarios y datos de prueba
├── pages/                 # Page Objects (POM)
│   ├── LoginPage.ts
│   ├── DashboardPage.ts
│   ├── ChatbotPage.ts
│   ├── RegistrationPage.ts
│   └── platform/             # Objetos de la plataforma QA (CommonJS)
├── components/            # Navegación Laravel y editor de metadatos QA
├── tests/                 # authentication · authorization · security · functional · api · integration · regression
├── fixtures/              # users.fixture.ts · database.fixture.ts
├── utils/                 # ApiClient · DatabaseHelper · EvidenceManager
├── reports/               # Evidencias y reportes de ejecución
├── playwright.config.ts
├── tsconfig.json
├── package.json
└── README.md
```

## Puesta en marcha

```bash
cd E:\qa
npm install
npx playwright install
npx playwright test
npx playwright show-report
```

Variables de entorno:

- `APP_URL`: URL base de la aplicación Laravel (por defecto `http://localhost`).
- `QA_ENV`: entorno de pruebas (`local` por defecto).
- `QA_DB_HOST`, `QA_DB_PORT`, `QA_DB_DATABASE`: conexión a la base de datos de pruebas.

## Convenciones

- Los escenarios viven en `tests/` y expresan el comportamiento esperado.
- Los selectores y la interacción viven en los Page Objects (`pages/`).
- Las pruebas de autorización verifican el **rechazo desde el servidor** (HTTP), no solo la ausencia de botones.
- Un caso no ejecutable se registra como `NOT RUN` o `BLOCKED`, nunca como `PASS`.
- Las credenciales de prueba **nunca** son de producción.

## Estado del plan — histórico

1. ✅ Preparación de infraestructura y entorno.
2. ✅ POM de autenticación y paneles iniciales.
3. ⬜ Seguridad: automatizar BUG-001 … BUG-008 y sus regresiones.
4. ⬜ Funcionalidad: los 37 casos documentados.
5. ⬜ Integración continua: GitHub Actions.

Consulta `E:\laragon\www\Proyecto2` para el código de producción (Laravel) y este documento para la suite de automatización.

## Avance del 16/11/2026

BUG-002 corregido y validado con controlador y formulario reales: ver QA_CIERRE_BUG002_20261116.md. La nueva ejecución tiene 54 PASS (18 por navegador), más una validación HTTP/MySQL independiente. Los resultados anteriores son históricos y se conservan.

Catálogo canónico: config/test-catalog.json y docs/CATALOGO_Y_TRAZABILIDAD_20261116.md. Validar con `node scripts/validate-catalog.cjs`. Contiene 37 casos originales: 17 con cobertura completa, 2 parcial y 18 pendientes; no equivale a 37 pruebas automatizadas ni a 37 PASS.

Antes de ejecutar, seguir docs/REPRODUCIR_CIERRE_20261116.md: el .env original apunta a gimnasia_unificada, por lo que las pruebas deben usar la instancia QA 127.0.0.1:8187 con guardia de gimnasia_unificada_test. No ejecutar contra la base original ni sobrescribir carpetas de reportes anteriores.

## POM vigente

Las páginas activas de Laravel son LoginPage, DashboardPage, ChatbotPage y RegistrationPage. DashboardPage compone Navigation para el cierre de sesión. Se retiraron cinco páginas sin consumidores y los componentes Modal/Notifications sin uso; esto no agrega cobertura UI para esos módulos.

Los scripts de validación de la plataforma reutilizan PlatformPage, SuitesPage y MetadataEditor en CommonJS, sin exigir compilación TypeScript para ejecutarlos. Los escenarios conservan sus aserciones, verificaciones HTTP y evidencias; los objetos encapsulan los controles y flujos compartidos. Los selectores específicos de verificaciones de seguridad, informes o diagnósticos pueden permanecer en sus escenarios.

Las pruebas HTTP/PHPUnit no requieren Page Objects. El ejemplo Selenium/Python permanece independiente de la suite Playwright. Tras cambiar el código QA, reiniciar los servicios antes de nuevas ejecuciones para actualizar la huella de implementación.

Verificación POM aislada: `npm run qa:test-pom`. Usa un catálogo temporal y un navegador; no ejecuta Laravel/MySQL ni envía correo. Requiere Chromium de Playwright instalado; alternativamente, definir `QA_POM_BROWSER_CHANNEL=msedge` para utilizar Edge instalado.

## Diagnóstico con Gemini

Crear una API key propia en https://aistudio.google.com/apikey y ejecutar `npm run qa:configure-gemini`: solicita la clave sin mostrarla y actualiza solo los campos de IA en `.env.local`. Ejecutar `npm run qa:ai:check` para comprobar una respuesta con datos sintéticos y reiniciar el backend. En Operaciones y calidad, seleccionar una ejecución terminada, marcar «Solicitar IA externa configurada» y pulsar «Explicar ejecución».

El proveedor usa `gemini-3.5-flash-lite` por defecto, configurable con QA_GEMINI_MODEL. Su nivel gratuito tiene límites y depende de la cuenta; no se activa facturación desde esta integración. Si tu proyecto Google tiene facturación habilitada, pueden aplicarse sus tarifas. El nivel gratuito puede usar el contenido enviado para mejorar productos. Solo se envían IDs, estados, métricas y huellas depuradas; no se envían formularios, binarios ni trazas completas. Esto limita el detalle del diagnóstico: las hipótesis no prueban la causa.

La clave permanece en el backend, viaja en el encabezado x-goog-api-key y no se agrega a URLs, reportes o almacenamiento del navegador. Una cuota agotada, respuesta incompleta o error de conexión conserva el análisis local por reglas. La IA no ejecuta comandos, modifica pruebas ni cambia PASS/FAIL. OpenAI sigue disponible para configuraciones previas mediante QA_AI_PROVIDER=openai.

Referencias: https://ai.google.dev/api/generate-content y https://ai.google.dev/gemini-api/docs/pricing.
