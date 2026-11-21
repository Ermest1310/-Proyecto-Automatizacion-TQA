# Informe de mejoras: cobertura y arranque

Fecha de trabajo: 16/11/2026, America/La_Paz. Las marcas de ejecución se guardan en UTC; las de 17/11 a las 03:xx UTC corresponden al 16/11 local.

## Resultado

Se ampliaron los casos del plan original y se instaló un comando de arranque con diagnóstico, manteniendo el proyecto existente en `E:\qa`.

- Cobertura implementada: 36 casos originales completos y 1 parcial/bloqueado (TC-34). Antes: 17 completos, 2 parciales y 18 sin automatizar.
- Veinte escenarios nuevos, sin reemplazar los 18 existentes. Catálogo total: 44 ejecutables, incluyendo perfiles y demostraciones separados.
- Un comando `npm run qa:start`; consulta `qa:doctor` y `qa:status`; cierre `qa:stop` de servicios propios.
- Panel autenticado de diagnóstico con disponibilidad, acciones y limitaciones explícitas.
- TC-06 y TC-37 actualizados conforme a las decisiones del usuario, con nombres y criterios originales conservados en el historial de aceptación.
- TC-34 bloqueado por falta de referencia autorizada, confirmado por el usuario. Se verifican controles parciales, sin afirmar que se completó MediaPipe.

## Ejecución instalada

ID: `qa-2026-11-17T03-50-23-112Z-224f022472a8`.

| Alcance | PASS | FAIL | NOT RUN / BLOCKED | SKIPPED |
|---|---:|---:|---:|---:|
| Veinte casos nuevos, proyecto Chromium / PHPUnit aislado | 19 | 0 | 1 | 0 |

El único NOT_RUN es QA-PLAN-34 / TC-34, cuyo log explica la falta de vídeo/oráculo. La corrida tiene estado global `infrastructure_error` porque no puede completarse ese caso; no es un defecto nuevo de la aplicación ni se oculta mediante skip. Las comprobaciones parciales de TC-34 se ejecutaron antes de declarar el bloqueo.

Los diecinueve PASS incluyen registro con tildes, edad mínima, duplicidad, fronteras de correo, permisos, PDF del atleta correcto, portal/chat, programación y límites de sesiones, rechazo de PHP con control positivo y los dos planificadores con oráculos independientes de fixtures acotados.

El registro instalado generó PDF y HTML y se confirmó recepción SMTP local. Los artefactos y la ejecución se conservaron sin reemplazar los históricos. Evidencia de la comprobación visual: `reports/coverage-validation-1794887574349/`.

La primera comprobación instalada perdió transitoriamente una conexión de lectura ECONNRESET mientras las pruebas continuaban. Se verificó que el backend seguía activo y que las pruebas habían terminado, y se recuperó la revisión con `--resume` sobre el mismo ID, sin repetir casos, POST ni correos. El script solo permite reintentos limitados de lecturas para ese error de red; no reintenta fallos HTTP o aserciones.

## Arranque y diagnóstico comprobados

1. Con MySQL apagado: arranque disponible en modo aislado, backend y buzón propios; el panel indica que HTTP está bloqueado.
2. Repetición de qa:start: mismo supervisor, sin procesos duplicados.
3. Cierre: se detuvieron servicios propios y un servidor ajeno de control continuó funcionando.
4. Con MySQL disponible: se inició PHP desde el lanzador y el health confirmó driver MySQL y `gimnasia_unificada_test`; el diagnóstico pasó a HTTP disponible. Evidencia en `reports/startup-live-1794887642377/`.
5. La protección del control usa ACL nativas de Windows; no se modificó la política de ejecución de scripts.

MySQL permanece administrado por Laragon. Para comprobar el segundo modo se inició temporalmente el servidor y se realizaron sondas/health de QA, sin migraciones ni escrituras de negocio. Se apaga de forma controlada solo si fue iniciado por esta validación. Los servicios reutilizados o ajenos no se cierran por qa:stop.

## Controles adicionales

- 38/38 comprobaciones Node del sistema aprobadas, incluidas cinco nuevas de diagnóstico, privacidad y coherencia de cobertura, y una del motivo específico de bloqueo en informes.
- TypeScript sin errores, catálogo validado y escaneo de valores secretos sin hallazgos.
- Descubrimiento de 114 resultados (38 casos por tres proyectos), sin afirmar ejecución de los 114 en esta ronda.
- Los nuevos casos de servidor usan migraciones reales únicamente en SQLite en memoria, claves foráneas activas antes y después de migrar, fixtures sintéticos, fecha controlada y HTTP externo prohibido.
- Las pruebas con MySQL real de negocio y la equivalencia entre motores no se infieren de estos resultados SQLite; las diferencias previamente documentadas se mantienen.

## Pendiente explícito

TC-34 necesita un vídeo de prueba autorizado y un resultado de referencia para validar la inferencia MediaPipe y la evaluación completa. Hasta disponer de ambos no se marcará como completo. La automatización actual comprueba la vista, carga, cálculo del servidor, cierre e inmutabilidad posterior, y declara su bloqueo restante.

La guía operativa está en ARRANQUE_Y_COBERTURA.md y la matriz de los 37 casos en COBERTURA_37_VIGENTE.md. Los archivos reemplazados tienen respaldo en `.runtime/backups/cobertura-arranque-*`; no se modificaron las reglas de Laravel, credenciales ni datos originales.
