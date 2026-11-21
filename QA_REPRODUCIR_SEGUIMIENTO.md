# Reproducir la segunda revisión

Desde `E:\qa`, con Laravel local en `http://Proyecto2.test`, usuarios QA configurados y base del servidor confirmada como `gimnasia_unificada_test`:

```powershell
node node_modules/typescript/bin/tsc --project tsconfig.json
$env:QA_REPORT_DIR = 'reports/followup/chromium'
Remove-Item Env:PLAYWRIGHT_BROWSERS_PATH -ErrorAction SilentlyContinue
node node_modules/@playwright/test/cli.js test --project=chromium

$env:QA_REPORT_DIR = 'reports/followup/cross'
$env:PLAYWRIGHT_BROWSERS_PATH = 'C:\Users\asbor\Documents\Codex\2026-11-12\de-e\work\browsers'
node node_modules/@playwright/test/cli.js test --project=firefox --project=webkit
```

Ejecutar secuencialmente para respetar los límites de login. Las descargas Firefox/WebKit ya están en el directorio indicado; si se cambia, instalar allí los navegadores con `playwright install firefox webkit`.

Requisitos adicionales: PHP CLI con `pdo_sqlite`, `fileinfo` y las dependencias Composer de Laravel. El helper usa por defecto `E:\laragon\www\Proyecto2`; se puede definir `QA_APP_SOURCE` si cambia el proyecto y `QA_PHP` si el ejecutable PHP no está en PATH. No se cambió package.json ni composer.json.

Los casos BUG-002/005/008 arrancan PHPUnit a través de Playwright; BUG-004/006 añaden controles PHPUnit al navegador. Se carga el kernel y código Laravel real, pero los datos se crean en SQLite `:memory:` y todo storage se redirige a la carpeta de resultados antes del bootstrap. Se comprueba el driver y nombre de BD. No usan MySQL ni modifican código de producción. No suponen cobertura adicional de navegador por repetirse en tres proyectos. En modo unitario Laravel omite CSRF; el alcance de estos casos es autorización, validación y persistencia. Las pruebas API del servidor web comprueban CSRF real.

BUG-002 debe producir FAIL mientras Apellido excluya `&`, porque TC-03 requiere conservarlo. La suite conserva salida no cero; no se cambia el requisito para volverla verde. Solo errores de infraestructura deben clasificarse BLOCKED.

Cada resultado backend adjunta JUnit, stdout y JSON. Las fixtures y sus archivos seguros se conservan en la carpeta de evidencias. No se limpian tablas ni se eliminan archivos existentes.

No ejecutar el helper legado `DatabaseHelper.run`: no se usa en estas pruebas. No desactivar middleware ni protecciones del servidor para forzar resultados.

La validación final se guardó como una sola ejecución secuencial de los tres proyectos:

```powershell
$env:PLAYWRIGHT_BROWSERS_PATH = 'C:\Users\asbor\Documents\Codex\2026-11-12\de-e\work\browsers'
$env:QA_REPORT_DIR = 'reports/followup/final'
node node_modules/@playwright/test/cli.js test
```

Ese directorio de navegadores contiene Firefox/WebKit descargados y enlaces locales a Chromium ya instalado. El runner necesita acceso a los CDN originales de la aplicación. El sandbox bloqueó esos CDN en una ejecución intermedia; se conserva su traza. Los controles backend también redirigen public_path a la carpeta de evidencias, para evitar escribir en el directorio público original incluso si se reintroduce una carga insegura.
