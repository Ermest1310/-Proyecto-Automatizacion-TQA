# Ampliación de cobertura del sistema

## Estado

Esta ampliación agrega una segunda fase de pruebas: 23 casos QA-EXT y 104 QA-DEEP de cobertura modular, operaciones y reglas de negocio, a los 44 ejecutables existentes. El total del catálogo pasa a 171. Los casos originales TC-01..TC-37 y sus resultados históricos no se reemplazan. Su matriz continúa en 36 completos y 1 parcial porque TC-34 requiere un video de referencia autorizado para validar la inferencia MediaPipe.

Los escenarios ampliados se ejecutan a través del kernel HTTP real de Laravel, con migraciones reales sobre SQLite en memoria, fecha de prueba controlada, almacenamiento aislado y solicitudes externas bloqueadas. El flujo de postulaciones crea y rechaza un registro sintético dentro de esa base temporal. Ninguno de estos casos conecta a MySQL ni cambia datos de la aplicación original.

## Módulos incluidos

| IDs | Área | Comprobación añadida |
|---|---|---|
| QA-EXT-01 | Roles y paneles | Paneles del administrador, entrenador, médico, fisioterapeuta, gimnasta y juez |
| QA-EXT-02 | Cuenta | Recuperación de contraseña, consulta del perfil y rechazo de datos inválidos sin modificar la cuenta |
| QA-EXT-03 | Entrenador | Panel, listado de gimnastas, sesiones, formulario y calendario |
| QA-EXT-04 | Gimnasta | Progreso, entrenador, sesiones, historial y calendario |
| QA-EXT-05 | Solicitudes de nivel | Bandejas para gimnasta, entrenador y administrador |
| QA-EXT-06 | Sesiones | Listado con filtros, historial y formulario de creación |
| QA-EXT-07 | Administración | Usuarios, roles y directorios por tipo de usuario |
| QA-EXT-08 | Catálogos deportivos | Aparatos, competencias y entrenamientos, incluido el endpoint relacionado |
| QA-EXT-09 | Postulaciones | Registro, consulta por administración, detalle y rechazo persistido en SQLite temporal |
| QA-EXT-10 | Área médica | Fisioterapia, nutrición, psicología, medicina y rehabilitación |
| QA-EXT-11 | Lesiones | Listado, formulario de creación y consulta de gimnastas |
| QA-EXT-12 | Rehabilitación | Listado de lesiones y planes; acceso clínico denegado al rol gimnasta |
| QA-EXT-13 | Evaluaciones clínicas | Formularios de aptitud, nutrición, respiración, psicología y fisiología |
| QA-EXT-14 | Video y movimiento | Análisis, carga, FIG, mediciones, ángulos, estadísticas y panel de movimiento |
| QA-EXT-15 | Juez | Panel, creación de evaluación y pendientes |
| QA-EXT-16 | Sistema experto | Consulta de reglas autorizada y rechazo al rol gimnasta |
| QA-EXT-17 | SEPI | Formulario y rechazo del valor de ansiedad fuera de rango |
| QA-EXT-18 | Minería de datos | Resumen, clustering, patrones, correlaciones y predicción |
| QA-EXT-19 | Rutinas | Acceso al planificador |
| QA-EXT-20 | Reportes | Reportes, informe completo y respaldos para administración |
| QA-EXT-21 | Seguridad | Denegación de seis rutas administrativas o médicas al rol gimnasta |
| QA-EXT-22 | Seguridad de APIs | Denegación por rol en APIs de feedback IA, estadísticas y diagnóstico SEPI |
| QA-EXT-23 | Privacidad | Un gimnasta no puede leer el video privado de una evaluación |

## Cómo encontrarlos

En Ejecutar pruebas, elegir el tipo y seleccionar Todas las categorías para ver en una lista los escenarios compatibles con el mismo ejecutor. Las pruebas de rendimiento HTTP, demostración y configuración suplementaria permanecen separadas porque usan ejecutores distintos. También se puede cambiar a Casos seleccionados y buscar por ID o texto; las etiquetas del catálogo indican palabras clave por módulo.

La suite ampliada aparece como expanded-suite en el catálogo y usa los IDs QA-EXT-* y QA-DEEP-*. Puede ejecutarse completa o por caso. Cada caso tiene su propio resultado e historial. Los casos profundos prueban aprobaciones, duplicados, sesiones, evaluación médica, SEPI, rehabilitación, capturas, FIG, sistema experto, usuarios, roles y revisión ML. El total de ampliación es 127: 23 QA-EXT y 104 QA-DEEP.

## Alcance y siguiente expansión

Esta primera pasada amplía la cobertura transversal de pantallas, rutas, permisos y dos flujos con validación/persistencia. Un HTTP 200 confirma que una vista puede abrirse con el fixture aislado; no demuestra por sí solo todas las reglas de negocio de esa pantalla.

La siguiente pasada debería añadir ciclos CRUD con fixtures por entidad para usuarios/roles, lesiones, evaluaciones, reglas expertas y mediciones; y pruebas de autorización por propiedad para registros privados. MediaPipe requiere el video de referencia y el resultado esperado antes de automatizarse como PASS.
