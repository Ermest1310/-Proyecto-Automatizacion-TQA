# Informe de entrega - correo automático y flujo completo

Fecha: 16/11/2026. Proyecto instalado: `E:\qa`. Recepción acordada: buzón SMTP local. No se enviaron mensajes externos ni se modificó Laravel o la base original.

## Implementado

- Configuración protegida mediante `.env.local`; ejemplo sin secretos y modo externo con autenticación y TLS obligatorio.
- Envío automático de PDF y HTML al terminar una ejecución y generar ambos documentos. Se verifican tamaño y SHA-256 antes del envío.
- Registro durable de intentos, tiempos, versión de informes, Message-ID, hashes y códigos de error, sin respuestas SMTP brutas ni credenciales.
- Cola serial, deduplicación, máximo tres intentos y tratamiento de errores, rechazos e incertidumbre. El correo mantiene un estado independiente de las pruebas.
- Reintento explícito desde la interfaz, control de duplicados y descarga de adjuntos realmente recibidos en el buzón local.
- Casos de demostración PASS y FAIL intencional separados de las pruebas de aplicación. No se alteran criterios de los defectos originales.

## Verificación instalada real

| ID de ejecución | Casos | PASS | FAIL | NOT RUN | Correo |
|---|---|---:|---:|---:|---|
| qa-2026-11-16T21-20-00-720Z-e9b0df802fcf | QA-SEC-002 / BUG-002, PHPUnit con SQLite en memoria | 1 | 0 | 0 | received_local |
| qa-2026-11-16T21-20-42-604Z-3007f28b6bb9 | QA-DEMO-PASS y QA-DEMO-FAIL, Chromium aislado | 1 | 1 | 0 | received_local |

Total de esta validación instalada: 2 PASS, 1 FAIL intencional, 0 NOT RUN, 0 SKIPPED. Se recibieron dos mensajes MIME, con dos adjuntos cada uno: cuatro archivos verificados por SHA-256 contra sus documentos originales. Cada mensaje requirió un único intento. El FAIL se conserva en el resultado, PDF, HTML y correo; no representa un defecto nuevo de Laravel.

La ejecución anterior de preparación está en la copia de trabajo y se distingue de estas dos ejecuciones instaladas. No se declara una nueva ejecución de los 54 resultados de la suite original.

La evidencia instalada se conserva en `reports/delivery-validation-1794863995076/`: JSON, capturas de la web y recepción, y los adjuntos descargados. Los originales de los correos están en `reports/mailbox/`; los registros de entrega están en `reports/runs/{id}/mail/`. Los mensajes no se incluyeron como EML bruto en el paquete compartible.

## Comprobaciones

- 32 pruebas Node del sistema aprobadas, incluidas ocho de configuración y entrega: TLS, falta de configuración, reintentos, incertidumbre, integridad, recepción SMTP real, rechazo 550 y recibo local ilegible.
- TypeScript sin errores; catálogo validado; escaneo de valores secretos del código sin hallazgos.
- Selección de casos y ejecución desde la web autenticada, generación automática, aceptación SMTP y recepción local comprobada.
- Descarga del PDF/HTML desde el buzón, igualdad de hashes y HTML recibido abierto sin conexión ni peticiones HTTP.
- Interfaz móvil sin desbordamiento y sin errores JavaScript.
- Suite original conservada: 18 casos por navegador, descubrimiento de 54 resultados. Los dos casos demo usan configuración separada y no se incluyen silenciosamente en la aplicación.
- Dependencias fijadas en lockfile; auditoría npm de producción sin vulnerabilidades reportadas durante esta revisión. La auditoría no sustituye las pruebas de aplicación.

## Privacidad e historial

Las credenciales originales se conservaron. Se agregaron únicamente parámetros del buzón local a la configuración privada, con respaldo protegido. Los archivos reemplazados tienen copia en `.runtime/backups/fase910-*`; no se reemplazaron informes anteriores ni se borraron registros.

El correo adjunta exclusivamente los informes depurados de las fases 7 y 8. No se adjuntan datos personales, credenciales, capturas privadas, logs completos o bases de datos. Las evidencias JSON de demostración se conservan en privado y se filtran al exportar. Los mensajes de error publicados describen la categoría del problema sin imprimir respuestas que puedan contener datos sensibles.

## Operación y alcance

Guía de arranque, configuración, estados, reintentos y demostración: `docs/CORREO_Y_DEMOSTRACION.md`. Comandos básicos: `npm run qa:inbox`, `npm run qa:backend`; visitar `http://127.0.0.1:9876/` y usar `demo@localhost.test`.

La recepción confirmada es local. El modo SMTP externo está implementado con TLS y credenciales privadas, pero no se verificó llegada a Gmail/Outlook ni se requiere para la demostración elegida. Una aceptación SMTP externa se identifica como smtp_accepted, nunca como recepción confirmada.

Los servicios temporales de validación se cierran al finalizar. La cobertura de los 37 casos originales permanece en 17 completos, 2 parciales y 18 pendientes; completar esos casos y otros entregables académicos sigue siendo trabajo separado de estas fases.
