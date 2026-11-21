# Configuración y datos de QA

La configuración activa se obtiene de variables de entorno. `.env.local` es privado y no se versiona; `.env.example` contiene nombres, valores no sensibles y campos vacíos. Las variables ya exportadas prevalecen sobre el archivo. Requiere Node >=22 (validado en 24.22.1) y PHP con pdo_mysql, pdo_sqlite y fileinfo.

En esta instalación se migraron las credenciales QA existentes a `.env.local`, sin imprimirlas ni cambiarlas en la aplicación. No copie la plantilla sobre ese archivo. En una instalación nueva, copie `.env.example` solo si no existe `.env.local` y complete sus valores localmente. El token API debe generarse con el comando de la plantilla; no compartirlo ni ponerlo en una URL.

Variables usadas: `APP_URL`, `QA_APP_SOURCE`, `QA_PHP`, `QA_DB_HOST`, `QA_DB_PORT`, `QA_DB_DATABASE`, `QA_DB_USER`, `QA_DB_PASSWORD`, credenciales/IDs `QA_ADMIN_*`, `QA_JUDGE_*`, `QA_ATHLETE_*`, `QA_GUEST_*`, `QA_API_TOKEN`, `QA_BACKEND_PORT`, `QA_RUN_TIMEOUT_MS`, `PLAYWRIGHT_BROWSERS_PATH`. El ejemplo Selenium legado usa `QA_DEMO_USERNAME/PASSWORD`; no forma parte de la suite habitual.

Los datos de pruebas deliberadamente inválidos y los tokens ficticios de tests unitarios no son credenciales operativas. Las contraseñas reales de las cuentas QA ya no se almacenan en esos archivos fuente. Los historiales, archivos ZIP legados y copias anteriores se conservan como privados/ignorados; no se borraron ni se afirma que hayan sido saneados retroactivamente.

## MySQL aislado

Solo se admite `gimnasia_unificada_test`. La instancia PHP configura la conexión antes del bootstrap, rechaza configuración Laravel cacheada y verifica `SELECT DATABASE()`. No se modifica el `.env` original de Laravel, que apunta a `gimnasia_unificada`.

Se creó una cuenta MySQL del runner con acceso desde 127.0.0.1 exclusivamente a la base de pruebas: SELECT, INSERT, UPDATE, DELETE y CREATE TEMPORARY TABLES. No tiene permisos de DDL persistente ni acceso a la base original. Su contraseña generada está solo en `.env.local`; no se modificaron credenciales de la aplicación original. La concesión de esa cuenta es configuración de seguridad del servidor, no una migración de tablas de negocio.

`DatabaseHelper` solo permite lecturas, usa `execFileSync` sin shell, comprueba la base y pasa la contraseña mediante el entorno del proceso. `run()` rechaza escrituras. Los fixtures con cambios de datos deben ser explícitos y aislados.

## Evidencias y redacción

Los logs y resultados expuestos por la API redactan valores secretos conocidos, cabeceras de autenticación/cookies y patrones de claves. Los runs orquestados desactivan trazas y vídeo para no persistir tráfico de autenticación. Las capturas de fallos y demás evidencias internas quedan bajo `reports/`, no se sirven por una ruta de archivos arbitraria. Los reportes Word/PDF y HTML autocontenido para compartir siguen siendo fases posteriores; deberán revisar también las imágenes antes de distribuirlas.

`npm run qa:secrets-check` revisa que los valores privados configurados no aparezcan en las fuentes activas. No imprime los valores y excluye `.env*`, `.runtime`, reports, node_modules y archivos históricos. No equivale a un escáner universal de secretos desconocidos.

## Operación

1. Iniciar MySQL de Laragon.
2. En una consola de `E:\qa`: `npm run qa:app`. La instancia queda en 127.0.0.1:8187 y guarda metadata privada bajo `.runtime`.
3. En otra consola: `npm run qa:backend`.
4. Ejecutar mediante `npm run qa:run -- case QA-SEC-002,QA-SEC-005 chromium` o una categoría documentada.
5. `npm run qa:audit-db` audita la conexión y diferencias de motores sin migraciones ni cambios persistentes de tablas.

Los casos solo SQLite pueden ejecutarse sin la instancia HTTP/MySQL. Los de navegador/API requieren la comprobación de salud autenticada de la instancia QA. La validación suplementaria de registro usa la configuración explícita de Chromium y conserva dos postulaciones sintéticas; respetar su límite de 3 registros/hora por IP.
