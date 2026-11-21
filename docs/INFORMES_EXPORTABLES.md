# PDF y HTML independiente - fases 7 y 8

## Uso desde la interfaz

Iniciar el backend con `npm run qa:backend` y abrir `http://127.0.0.1:9876/`. Al finalizar una ejecución nueva, el backend genera automáticamente PDF y HTML. En su resultado aparece «Informes de esta ejecución», con estado de generación y los botones de descarga. Los resultados históricos se exportan con «Generar PDF y HTML» sin volver a ejecutar las pruebas. El mismo botón crea otra versión conservando las anteriores.

No se necesita MySQL ni Laravel para exportar una ejecución ya guardada. Se necesita Chromium instalado para crear el PDF (`npx playwright install chromium`, si aún no existe). Se usa el Chromium de Playwright disponible en el proyecto, sin añadir librerías. El PDF se compone desde el mismo modelo y contenido HTML para evitar divergencias de estados y totales.

## Contenido de ambos formatos

- Identificador y estado de ejecución, inicio/fin en UTC, duración y fecha de generación.
- Tabla de casos y proyectos con estados, observaciones, duración y totales calculados desde los casos, no desde un resumen copiado.
- ID de automatización y TC originales; objetivo, precondiciones, pasos del catálogo y resultado esperado.
- Resultado observado, explicación de fallo y métricas con criterios cuando existen.
- Pasos instrumentados separados de los pasos planificados. Se indica expresamente cuando el runner no registró pasos o no hay evidencias atribuibles.
- Extractos estructurados de evidencia y SHA-256 del archivo original privado para contrastarlos localmente. No se inventan capturas.

El PDF tiene tamaño A4, páginas numeradas y encabezados de tabla repetidos. El HTML es un único archivo UTF-8 con estilos incluidos, enlaces internos y sin JavaScript, fuentes externas, CDN ni peticiones de red. Se abre directamente con doble clic y funciona sin conexión. El contenido puede imprimirse desde el navegador.

## Privacidad

El modelo exportable excluye el correo destinatario, datos de prueba, entradas de formularios, registros personales, cuerpos de respuestas y errores/trazas en bruto. Las credenciales cargadas en la configuración se redactan; los correos y rutas privadas de texto descriptivo también se omiten. Los nombres, documentos, datos físicos y fechas de los registros persistidos no se exportan.

Los JSON se procesan mediante una lista limitada de campos técnicos, booleanos, conteos y estados HTTP. Las muestras extensas se resumen en conteos. Las imágenes, trazas, XML y logs privados se representan por tipo, tamaño y huella; no se incrusta su contenido sin una depuración específica. Esta decisión evita que una captura de la aplicación publique información personal. Los originales permanecen en la carpeta de evidencia privada.

El reporter nuevo conserva referencias relativas a adjuntos dentro de la evidencia de su ejecución. Para resultados anteriores, se utilizan únicamente archivos atribuibles por ID/caso/proyecto; los duplicados se eliminan del informe por hash, sin borrar los originales. Un archivo no atribuible no se asigna a otro caso.

## Estados, historial y recuperación

Cada generación crea una carpeta nueva:

```text
reports/runs/qa-ID/exports/report-FECHA-ALEATORIO/
  manifest.json
  report-data.json
  report.html
  report.pdf
```

`report-data.json` es el modelo depurado común; no se expone como descarga pública. `manifest.json` registra queued/generating/ready/partial/error y hashes de archivos terminados. Si el PDF falla, el HTML disponible permanece descargable y la interfaz informa el problema. Ningún fallo de exportación convierte una prueba fallida en aprobada ni altera su `run.json`.

Las generaciones se serializan, hay como máximo veinte trabajos activos/en cola y las solicitudes simultáneas del mismo caso de ejecución comparten el trabajo en curso. Los informes solo se generan para ejecuciones terminales; durante la ejecución se devuelve 409. Una generación interrumpida cuyo proceso ya no existe se marca partial/error al consultar y permite generar otra versión. Los archivos de versiones completas no se reemplazan.

Los documentos se descargan por rutas autenticadas que solo admiten formatos HTML/PDF y versiones válidas. No se sirve el directorio privado. La API mantiene los controles de sesión, CSRF y origen previos.

## API y CLI

```text
POST /api/runs/{id}/reports
GET  /api/runs/{id}/reports
GET  /api/runs/{id}/reports/{version}/html
GET  /api/runs/{id}/reports/{version}/pdf
```

POST devuelve 202 y el trabajo de exportación; consultar GET hasta ready/partial/error. La API admite autenticación Bearer o sesión local; las escrituras con sesión requieren CSRF.

```powershell
cd E:\qa
npm run qa:export -- qa-ID-DE-EJECUCION
```

La CLI lee el resultado sin recuperar ni modificar ejecuciones activas y no requiere un servidor iniciado. El nombre de instancia se respeta mediante QA_BACKEND_INSTANCE. Evitar lanzar varias CLI de exportación en paralelo; la serialización global se proporciona desde el backend.

## Alcance

Estos cambios completan la generación de PDF y HTML independiente. El correo automático ya está integrado; su configuración y recepción local se documentan en CORREO_Y_DEMOSTRACION.md. No se alteraron Laravel ni las bases de datos para generar los documentos.


## Actualización de correo - fase 9

El envío automático ya está implementado para ejecuciones nuevas. PDF y HTML se adjuntan tras finalizar ambos informes; correo y pruebas tienen estados separados. Los históricos no se envían al regenerarlos, salvo solicitud expresa. La recepción validada es SMTP local; el modo externo exige TLS y credenciales privadas. Consultar CORREO_Y_DEMOSTRACION.md.
