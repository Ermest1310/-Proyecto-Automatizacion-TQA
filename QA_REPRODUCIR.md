# Reproducir la validación

Ejecutar en PowerShell desde `E:\qa`. Confirmar antes que Laravel utiliza una base de datos de pruebas y usuarios QA, sin apuntar a datos originales. Esta ejecución comprobó `.env` con `DB_DATABASE=gimnasia_unificada_test` y ausencia de configuración Laravel cacheada; no cambia esa configuración.

```powershell
node node_modules/typescript/bin/tsc --project tsconfig.json
Remove-Item Env:QA_REPORT_DIR -ErrorAction SilentlyContinue
Remove-Item Env:PLAYWRIGHT_BROWSERS_PATH -ErrorAction SilentlyContinue
node node_modules/@playwright/test/cli.js test --project=chromium

$env:PLAYWRIGHT_BROWSERS_PATH = 'C:\Users\asbor\Documents\Codex\2026-11-12\de-e\work\browsers'
$env:QA_REPORT_DIR = 'reports/cross'
node node_modules/@playwright/test/cli.js test --project=firefox --project=webkit
```

Firefox y WebKit se descargaron en esa carpeta de trabajo. Para una instalación independiente, elegir un directorio permanente con `PLAYWRIGHT_BROWSERS_PATH` y ejecutar `node node_modules/@playwright/test/cli.js install firefox webkit`. No se cambiaron dependencias de npm.

La restricción de procesos de este entorno impidió iniciar Firefox/WebKit dentro del sandbox; la ejecución autorizada fuera de esa restricción sí inició ambos navegadores. No confundir ese problema con un defecto de Laravel.

Se usa un worker y se respeta el `Retry-After` del servidor con un solo reintento de login. No ejecutar suites simultáneas contra la misma cuenta: comparten el límite del servidor (5 intentos por cuenta/minuto, 20 por IP/minuto).

Los casos BLOCKED generan un error con ese prefijo y mantienen salida no cero. Playwright los muestra como failed; el informe los distingue de FAIL de una aserción. No equivalen a skip ni a aprobación. La suite tiene bloqueos explícitos pendientes de implementar para BUG-002 y BUG-005 y una verificación parcial para BUG-008.

Evidencias de esta entrega: `reports/validacion-20261112-codex/`. Los reportes anteriores de `E:\qa\reports\html` y `reports\playwright` se conservan. Los archivos QA reemplazados están en `reports/backup-20261112-codex/`.

No invocar `DatabaseHelper.run` ni limpiezas de la BD: el helper legado tiene un valor por defecto de base original y no fue utilizado en esta validación. Configurar explícitamente una BD aislada antes de completar fixtures persistentes.
