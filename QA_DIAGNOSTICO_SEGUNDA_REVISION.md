# Auditoría de seguimiento antes de editar — 12/11/2026

Estado previo comprobado: las tres suites tienen 15 PASS / 3 BLOCKED, con salida no cero. BUG-002 y BUG-005 contenían bloqueos incondicionales y BUG-008 rechazaba PHP sin completar el resto del formulario. No eran regresiones completas.

Se identificó `tests/Feature/SecurityAccessTest.php` en Laravel, con fixtures SQLite `:memory:`. Se preparan pruebas QA externas que reutilizan ese patrón, ejercitan rutas/controladores/middleware reales y crean esquemas/datos exclusivamente en memoria. El directorio storage se redirige al resultado de cada prueba antes del bootstrap; no se usan migraciones de la base original ni se altera producción.

Diagnóstico confirmado en prueba preliminar: el control de registro sin ampersand persiste; `Apellido=Sotomayor & Vargas` devuelve 422, `errors.Apellido`, sin persistencia. Es un FAIL respecto al TC-03 original, con discrepancia de requisito a documentar, no un BLOCKED ni un motivo para cambiar Laravel.

BUG-005: fixture de solicitud pendiente de atleta ajeno; comprobar fila completa y niveles antes/después. Control positivo admin sobre la misma solicitud demuestra ruta y fixture válidas.

BUG-008: formulario completo de juez con atleta inscrito, competencia y aparato válidos; PHP inerte debe ser el único error; comprobar filas/archivos sin cambios. Control positivo con WebM real y seguro, validando bytes en almacenamiento privado.

BUG-004: completar control de descarga válida y archivo centinela fuera del directorio de backups, ambos en storage aislado.

BUG-006: complementar navegador con errores de proveedor/transporte/configuración controlados mediante Http fake en proceso PHPUnit. Sin red en esos controles. No son modificaciones del servicio de producción.

Las pruebas de navegador mantienen POM. Los casos de persistencia incorporan una capa de integración PHPUnit iniciada por Playwright; repetirlos por proyecto no constituye cobertura adicional de motores de navegador. Debe explicitarse en el informe.
