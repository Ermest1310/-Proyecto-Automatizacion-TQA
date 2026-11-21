# Correo automático y validación integral

## Arranque para la demostración local acordada

La configuración está en `.env.local` privado, con ACL de usuario y SYSTEM. No compartirlo ni copiarlo a entregables. `.env.example` solo describe variables, sin credenciales.

```dotenv
QA_MAIL_MODE=local
QA_SMTP_HOST=127.0.0.1
QA_SMTP_PORT=1025
QA_MAIL_FROM=qa@localhost.test
```

Abrir dos terminales en `E:\qa`:

```powershell
npm run qa:inbox
```

```powershell
npm run qa:backend
```

Abrir `http://127.0.0.1:9876/` y entrar con el token local existente. Usar un destinatario como `demo@localhost.test`. El buzón solo escucha en loopback, rechaza destinatarios fuera de ese dominio, no reenvía a Internet y no necesita credenciales SMTP. Los adjuntos recibidos quedan en `reports/mailbox/msg-*/`; desde la interfaz se pueden descargar como «Adjunto recibido PDF/HTML».

## Circuito completo

1. Escribir correo, tipo, categoría y casos.
2. Ejecutar desde la web. El runner mantiene PASS/FAIL/NOT_RUN separados.
3. Al finalizar, generar PDF y HTML mediante el exportador existente.
4. Solo cuando ambos están listos, verificar tamaño e integridad SHA-256, formar el mensaje MIME y enviarlo al destinatario de esa ejecución.
5. Registrar aceptación SMTP y, en modo local, verificar el recibo del buzón y los hashes de ambos adjuntos antes de declarar `received_local`.
6. Mostrar la entrega en la interfaz, separada de los resultados. Descargar los adjuntos desde el buzón y abrir el HTML sin conexión.

Los mensajes contienen únicamente ID, estado y los dos informes depurados. Nunca adjuntan `.env`, bases de datos, logs brutos, trazas, capturas privadas ni registros personales. Se deshabilita la lectura de rutas o URL por Nodemailer: recibe buffers de los dos documentos ya validados. Límite por adjunto 12 MiB y conjunto 15 MiB antes de codificación MIME. Las conexiones tienen tiempos acotados y no registran protocolo, autenticación o contenido en consola.

## PASS y FAIL controlado

La categoría funcional «Demostración controlada» contiene:

- QA-DEMO-PASS: página local muestra 4; aserción exige 4; PASS esperado.
- QA-DEMO-FAIL: página local muestra 4; aserción exige 5; FAIL intencional esperado para comprobar el circuito de errores.

Usan Chromium en un proyecto `demo` separado, sin Laravel ni base de datos. Se pueden seleccionar juntos desde la web: el resultado correcto de esa demostración es 1 PASS y 1 FAIL, con correo recibido y ambos informes conservando el FAIL. El informe explica expresamente que no es un defecto de Laravel. No se usa `skip`, reintentos de pruebas ni cambio de reglas para ocultarlo.

Los 18 casos de aplicación por navegador y su descubrimiento de 54 resultados permanecen separados. El catálogo ahora tiene 24 ejecutables: 18 comunes, 1 suplementario MySQL, 3 HTTP y 2 de demostración; los 37 casos originales mantienen sus vínculos y cobertura.

Para un PASS del sistema existente, seleccionar tipo funcional, categoría regresión y QA-SEC-002 (BUG-002). Usa PHPUnit y SQLite en memoria aislada; no exige iniciar el servidor HTTP o MySQL. Para otras categorías de aplicación que sí requieren HTTP, iniciar MySQL y `npm run qa:app` según las guías anteriores.

## Estados de entrega, errores y reintentos

Los archivos `reports/runs/{id}/mail/delivery.json` y `events.ndjson` registran ID del mensaje, versión de informes, intentos, tiempos, hashes, estados y códigos de error. No registran credenciales, host, destinatario ni respuesta SMTP bruta; el destinatario ya existe en la selección privada de la ejecución.

| Estado | Significado |
|---|---|
| not_requested | Aún no se solicitó entrega; esperar informes o solicitar envío de un histórico |
| not_configured | Correo deshabilitado, configuración inválida o falta destinatario compatible |
| queued / sending | Entrega en cola o en curso |
| smtp_accepted | El servidor aceptó el mensaje; no demuestra recepción en un buzón externo |
| received_local | Buzón local recibió MIME y ambos adjuntos coinciden por SHA-256 |
| failed | Error definitivo o fallo antes de confirmar entrega |
| uncertain | Se perdió una confirmación o el proceso se interrumpió durante envío; podría haberse recibido |
| interrupted | Proceso interrumpido antes de comenzar; no se reenvía automáticamente |

Un fallo de correo nunca altera `run.json`, los contadores PASS/FAIL, los documentos ni los defectos abiertos. Máximo tres intentos, cola serial de hasta veinte trabajos y deduplicación de solicitudes simultáneas por ejecución. La primera entrega es automática; los reintentos se hacen expresamente desde la interfaz. Se conserva la misma versión de adjuntos y Message-ID. Una entrega ya aceptada/recibida no se reenvía. Si el estado es incierto, la interfaz exige reconocer el riesgo de duplicado antes de reintentar; SMTP no ofrece una garantía universal de entrega exactamente una vez.

Los históricos no se envían automáticamente al regenerar informes. Se puede solicitar expresamente su primera entrega desde el panel. Al reiniciar no se reproducen envíos pendientes de forma ciega: los procesos muertos quedan como interrupted/uncertain y requieren revisión.

## Activar envío a cualquier correo con Gmail

El modo `local` solo entrega a `@localhost.test`; no reenvía mensajes a Internet. Para enviar a una dirección externa, configure un proveedor SMTP. Con Gmail, Google requiere verificación en dos pasos y una contraseña de aplicación. No use ni comparta la contraseña normal de la cuenta.

1. Cambie la contraseña normal de la cuenta si ya la compartió en un chat.
2. Active la verificación en dos pasos y genere una contraseña de aplicación para este proyecto.
3. Desde la carpeta del proyecto, ejecute `npm run qa:configure-gmail` y escriba esa contraseña en la solicitud oculta. El script configura Gmail SMTP con STARTTLS y guarda la credencial únicamente en `.env.local`; no la muestra ni envía mensajes.
4. Reinicie el stack con `npm run qa:stop` y `npm run qa:start`.
5. Envíe una ejecución a una dirección válida. En SMTP externo se permiten destinatarios externos; el estado `smtp_accepted` confirma que Gmail aceptó el mensaje, pero no garantiza que llegó a la bandeja de entrada.

Google documenta `smtp.gmail.com` con puerto 587 y TLS, y exige autenticación. Las contraseñas de aplicación requieren la verificación en dos pasos y tienen 16 caracteres: [configuración SMTP de Gmail](https://support.google.com/mail/answer/7104828?hl=es), [contraseñas de aplicación](https://support.google.com/mail/answer/185833?hl=es).

## Configuración SMTP manual

Configurar exclusivamente en `.env.local`, después reiniciar el backend:

```dotenv
QA_MAIL_MODE=smtp
QA_SMTP_HOST=
QA_SMTP_PORT=587
QA_SMTP_TLS=starttls
QA_SMTP_USER=
QA_SMTP_PASSWORD=
QA_MAIL_FROM=
```

Se exige autenticación y TLS: `starttls` exige la actualización TLS; `implicit` configura TLS directo, habitualmente en puerto 465. La validación de certificado permanece activa y el mínimo es TLS 1.2. No se reusan credenciales de Laravel ni se guardan claves en JS/HTML. Para deshabilitar correo: `QA_MAIL_MODE=off`. La llegada a Gmail/Outlook debe verificarse con un envío controlado después de configurar credenciales válidas.

Documentación de referencia: [SMTP Nodemailer](https://nodemailer.com/smtp), [seguridad y adjuntos](https://nodemailer.com/message), [SMTP local](https://nodemailer.com/extras/smtp-server).

## API y comprobación reproducible

```text
GET  /api/mail/config
GET  /api/runs/{id}/mail
POST /api/runs/{id}/mail
GET  /api/runs/{id}/mail/received/pdf
GET  /api/runs/{id}/mail/received/html
```

Autenticación y CSRF mantienen las reglas existentes. La configuración expuesta solo indica modo y disponibilidad. POST acepta únicamente `acknowledgeDuplicateRisk` booleano opcional; no admite remitente, destinatario alternativo, rutas, hosts ni adjuntos arbitrarios.

```powershell
node scripts/validate-delivery.cjs
```

Con buzón local y backend iniciados, la comprobación usa la web para ejecutar BUG-002 y luego la categoría de demostración, descarga los adjuntos recibidos, compara hashes y abre el HTML sin conexión. Conserva JSON y capturas en una carpeta nueva `reports/delivery-validation-*`. El FAIL programado forma parte del resultado esperado de esta validación; un fallo de recepción o de integridad hace fallar el script.
