# Reproducción de la validación

Desde E:\qa: `node scripts/validate-catalog.cjs` valida el catálogo sin ejecutar funcionalidad. `node node_modules/typescript/bin/tsc --project tsconfig.json` comprueba TypeScript.

Para repetir la suite, primero iniciar MySQL de Laragon. No cambiar el .env original. Crear un directorio QA_LIVE_STORAGE nuevo para una instancia de pruebas independiente y ejecutar:

```powershell
$env:QA_LIVE_STORAGE = Join-Path (Get-Location) ('reports/live-storage-' + (Get-Date -Format yyyyMMdd-HHmmss))
php -S 127.0.0.1:8187 -t E:/laragon/www/Proyecto2/public scripts/qa-live-router.php
```

En otra consola:

```powershell
$env:APP_URL = 'http://127.0.0.1:8187'
$env:QA_REPORT_DIR = 'reports/nueva-corrida-unica'
$env:PLAYWRIGHT_BROWSERS_PATH = 'C:/Users/asbor/Documents/Codex/2026-11-12/de-e/work/browsers'
node node_modules/@playwright/test/cli.js test
```

Para la prueba de registro real, transmitir en esa consola el mismo QA_LIVE_STORAGE de la instancia, configurar QA_DB_VERIFY_SCRIPT con la ruta absoluta de scripts/verify-registration-db.php y usar otra carpeta QA_REPORT_DIR:

```powershell
$env:QA_DB_VERIFY_SCRIPT = Join-Path (Get-Location) 'scripts/verify-registration-db.php'
node node_modules/@playwright/test/cli.js test --config=playwright.bug002.config.ts
```

Esta prueba requiere E:\laragon\www\Proyecto2 con MySQL/fixtures existentes y PHP con pdo_mysql/fileinfo. El helper backend requiere pdo_sqlite. La prueba HTTP crea y conserva dos postulaciones sintéticas en gimnasia_unificada_test y hace tres intentos de registro; respetar el límite de 3/hora por IP. No reutilizar una instancia agotada ni borrar sus límites para conseguir PASS.

Los nuevos resultados deben guardarse con nombre único. No apuntar APP_URL al servidor original configurado con gimnasia_unificada. El puerto 8187 tiene una guardia explícita de base de pruebas. Los campos QA_DB_VERIFY_SCRIPT y QA_LIVE_STORAGE son obligatorios para la validación suplementaria.
