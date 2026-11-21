# BUG-002 — rechazo de apellido con ampersand

**Estado:** abierto frente al criterio TC-03 del plan original. No se modificó Laravel.

**Endpoint:** `POST /register`, campo `Apellido`. La implementación actual registra una postulación pendiente; ya no crea directamente una cuenta.

**Reproducción aislada:** kernel HTTP real de Laravel, SQLite en memoria y directorios de trabajo propios. Datos válidos: Pedro, Bolivia, fecha de nacimiento 2010-05-05, masculino, Junior, 60 kg, 1.62 m, nivel Principiante. Cada envío usa email y cédula distintos.

| Entrada | Respuesta | Persistencia |
|---|---|---|
| `Sotomayor Vargas` | 302 a login | Postulación guardada con apellido íntegro |
| `Sotomayor & Vargas` | 422, `errors.Apellido`: formato inválido | Sin postulación para ese email |

**Causa actual:** la expresión regular de `AuthController::register` solo admite letras y espacios en `Apellido`. `SanitizeInput` actualmente deja pasar los datos; el rechazo observado no proviene del antiguo bloqueo genérico del sanitizador.

**Discrepancia:** TC-03 exige admitir y conservar ese apellido. La implementación actual lo prohíbe. El resultado FAIL es correcto respecto de ese criterio; no se debe convertir el 422 en PASS ni sustituir el caso por una prueba XSS.

## Propuesta para revisión, sin aplicar

Si TC-03 sigue siendo el requisito aprobado, ampliar explícitamente la validación de `Apellido` para admitir `&`, manteniendo límites de longitud, validación de los otros campos y escape de salida. No eliminar toda la validación ni reescribir el apellido para hacer pasar la prueba.

Si el requisito vigente prohíbe ese carácter, el responsable funcional debe aprobar y documentar el cambio en el plan y en el criterio de aceptación. Solo después corresponde actualizar la expectativa del test, conservando la trazabilidad del requisito anterior.

**Criterio de cierre:** la política de caracteres está aprobada; los controles válido/inválido producen el resultado acordado y se comprueba la integridad persistida. La prueba actual mantiene FAIL hasta resolverlo. Los JSON del control y del fallo y el JUnit se adjuntan al reporte HTML.
