# Validación MySQL de QA y diferencias con SQLite

La auditoría se ejecutó el 16/11/2026 contra gimnasia_unificada_test. Se inspeccionaron versión, SQL mode, charset/collation, tablas, columnas de postulaciones, 54 claves foráneas declaradas y las cuatro cuentas QA (unicidad, estado activo, rol e ID), sin publicar contraseñas o correos.

Motores observados: MySQL 8.4.3 y SQLite 3.51.3. Las tablas auditadas usan InnoDB. La auditoría puede repetirse con `npm run qa:audit-db`; no ejecuta migraciones y sus únicas escrituras de datos son una tabla TEMPORARY de la conexión MySQL y una base SQLite :memory:. No modifica tablas persistentes existentes.

| Prueba empírica | MySQL de QA | SQLite en memoria | Implicación |
|---|---|---|---|
| Texto abcdef en VARCHAR(2) | Rechazado, SQLSTATE 22001 con modo estricto actual | Aceptado | SQLite no sustituye validar longitud/esquema en MySQL |
| 1.239 en DECIMAL(5,2) | 1.24 | 1.239 | Validar redondeo y precisión del dominio en motor real |
| Comparar fila ok con OK | 1 coincidencia | 0 coincidencias | La collation/comparación puede cambiar duplicidad y búsquedas |
| Claves foráneas | 54 declaradas en esquema auditado | PDO SQLite por defecto estaba OFF en la conexión de sonda | Se activan en la sonda y se exige PRAGMA foreign_keys=1 en la fixture Laravel |

Se verificó además una cuenta de runner con permisos solo en la base de pruebas y se comprobó denegación al intentar leer una tabla de gimnasia_unificada. No se alteraron tablas ni credenciales de la aplicación original. El usuario de auditoría ya no requiere root en .env.local del runner.

Las regresiones PHPUnit usan esquemas mínimos en SQLite; pasan por rutas/controladores/middleware reales pero no certifican el esquema completo de MySQL ni concurrencia, bloqueos, deadlocks, rollback entre conexiones o planes de consultas. Las nuevas verificaciones HTTP del backend confirman el acceso a la instancia MySQL protegida. La validación previa de BUG-002 incluye persistencia real MySQL y permanece como evidencia independiente.

No se concluye que SQLite haya ocultado un defecto concreto adicional: se demostraron diferencias de comportamiento que requieren criterios y controles específicos al ampliar los casos pendientes. La documentación de los 37 casos conserva esa distinción.
