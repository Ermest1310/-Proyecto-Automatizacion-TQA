# Informe de entrega - fases 7 y 8

Fecha: 16/11/2026. Proyecto: `E:\qa`.

Se implementaron PDF y HTML independiente utilizando el backend, catálogo, resultados y evidencia existentes. No se añadieron dependencias ni se modificaron Laravel o bases de datos. El PDF se genera con Chromium de Playwright a partir del mismo contenido depurado que el HTML.

## Funcionalidad instalada

- Generación automática al terminar una corrida nueva, incluidas corridas fallidas, bloqueadas o canceladas.
- Exportación manual de resultados guardados desde la interfaz o CLI, sin ejecutar nuevamente las pruebas.
- Descargas autenticadas, estado de generación y versiones nuevas sin reemplazar documentos anteriores.
- Modelo común con totales recalculados, detalle, trazabilidad TC, pasos previstos e instrumentados diferenciados, resultados, criterios y evidencia técnica depurada por caso.
- Explicaciones que conservan FAIL y NOT_RUN, sin convertir una prueba aprobada en declaración automática de BUG corregido.
- HTML único con estilos incluidos y sin dependencias de red; PDF A4 con páginas numeradas.
- Recuperación de generaciones interrumpidas y separación entre un fallo de documento y el resultado de las pruebas.

## Resultados usados para verificar

1. `qa-2026-11-16T18-07-44-711Z-608547094912`: 1 PASS y 1 FAIL. El documento conserva el p95 de estabilidad de 2880,4 ms frente al límite 2000 ms.
2. `qa-2026-11-16T18-09-54-071Z-8c2939e5b58c`: BUG-002, 1 PASS. Los extractos muestran estados HTTP, controles negativos y presencia del registro, omitiendo el registro personal y sus entradas.
3. `qa-2026-11-16T19-12-43-714Z-3cde5a29963c`: prueba de integración del exportador con la aplicación deliberadamente apagada: infraestructura, 0 PASS y 1 NOT_RUN. Los documentos se generaron automáticamente sin alterar esa clasificación.

Los primeros dos resultados son históricos reutilizados para validar exportación; no se reportan como una ejecución nueva de pruebas. El tercer registro verifica explícitamente el comportamiento del sistema de informes ante bloqueo. No se ejecutó la suite de 54 resultados ni carga HTTP de negocio en esta fase.

## Comprobaciones

- 24/24 pruebas Node del sistema aprobadas, incluidas seis específicas de informes: privacidad, escape HTML, totales de una selección de 54 resultados simulada, versiones inmutables, fallo de PDF y recuperación de generación interrumpida.
- TypeScript sin errores; catálogo conserva 37 casos originales y 22 ejecutables; escaneo del código sin valores secretos encontrados.
- Descarga real de HTML y PDF desde la interfaz autenticada; acceso sin sesión rechazado.
- HTML descargado abierto mediante `file://` en contexto sin conexión: tabla de dos casos, totales 1/1/0/0, FAIL visible, cero peticiones HTTP y cero scripts. Verificado a 390 px sin desbordamiento de la página.
- Generación automática de documentos al finalizar el registro NOT_RUN; estados de pruebas conservados.
- PDFs renderizados con Poppler e inspeccionados visualmente: resumen, tablas, detalle, evidencia, paginación y ausencia de recortes o superposición. La extracción de texto y el escaneo de secretos complementan la revisión visual.

## Verificación instalada

La misma comprobación se repitió sobre la instalación E:\qa. El HTML y PDF se descargaron desde la web y el HTML volvió a abrir sin conexión. El registro adicional `qa-2026-11-16T19-17-38-973Z-d827f75bf783` quedó en infrastructure_error, 1 NOT_RUN y 0 PASS, con ambos documentos generados automáticamente. No se confunde con un defecto de aplicación: el servicio HTTP estaba deliberadamente apagado. La evidencia de integración está en `reports/export-validation-1794856655151/`.

## Privacidad y límites

Se excluyen destinatarios, credenciales, datos de formularios, registros personales, respuestas completas y trazas. La evidencia estructurada se filtra por campos técnicos permitidos; logs, XML, capturas y otros binarios se referencian por huella SHA-256, sin publicar su contenido bruto. Los originales no se borran. Si no hay evidencia atribuible a un caso, el documento lo indica. Los detalles de esta política se encuentran en INFORMES_EXPORTABLES.md.

Los PDFs de rendimiento describen mediciones locales breves y no acreditan capacidad en producción. Los generadores no envían correo. Si falta Chromium o falla el PDF, el sistema informa partial/error, conserva el HTML disponible y mantiene intactos los resultados de pruebas.

## Archivos principales

- `backend/report-model.cjs`, `backend/report-template.cjs`, `backend/reports.cjs`.
- Integración en `backend/manager.cjs`, `backend/server.cjs`, `backend/playwright-reporter.cjs`.
- Descargas y estado en `web/index.html`, `web/app.js`, `web/app.css`.
- `scripts/generate-reports.cjs`, `scripts/validate-exports.cjs`, `backend/tests/reports.test.cjs`.
- Comando `qa:export` en package.json, README y guías de uso.

Los archivos reemplazados se respaldan en `.runtime/backups/fase78-*`. Los documentos generados se guardan debajo de `reports/runs/{id}/exports/` en nuevas versiones. Los servicios temporales de validación se detienen al finalizar; la instalación queda lista para arrancar con `npm run qa:backend`.
