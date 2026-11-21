# Arranque, diagnóstico y cobertura ampliada

## Un comando de arranque

```powershell
cd E:\qa
npm run qa:start
```

Abrir `http://127.0.0.1:9876/`. El comando inicia el ejecutor y, en modo local, el buzón SMTP. Si MySQL de Laragon ya está disponible, también inicia la aplicación QA y exige que su health confirme `gimnasia_unificada_test`. Si MySQL está apagado, informa modo aislado: las pruebas PHPUnit/SQLite y la demostración siguen funcionando; las pruebas HTTP no se presentan como disponibles.

MySQL continúa administrado por Laragon: este lanzador no lo instala, inicia, migra ni apaga. Para habilitar HTTP después de encender MySQL, ejecutar `qa:stop` y `qa:start`. La aplicación sigue utilizando el usuario restringido y los guardas de aislamiento anteriores.

```powershell
npm run qa:doctor
npm run qa:status
npm run qa:stop
```

`qa:doctor -- --json` devuelve el mismo diagnóstico sin credenciales. Comprueba Node, PHP, pdo_sqlite, pdo_mysql, fileinfo, PHPUnit, motores de navegador, configuración aislada, conectividad MySQL, identidad de la aplicación, ejecutor y buzón de este proyecto. No envía correos ni ejecuta migraciones.

La interfaz incorpora «Estado del entorno», con disponibilidad, acciones sugeridas y el caso de MediaPipe bloqueado. La cobertura implementada se muestra separada de los resultados de ejecución. El diagnóstico requiere autenticación y se almacena en caché quince segundos para evitar sondas repetidas.

## Ciclo de vida y permisos

Repetir `qa:start` no crea copias del conjunto. Se identifican servicios por huella del proyecto; un puerto ocupado por otro servicio se rechaza. Los servicios ya disponibles de este proyecto se pueden reutilizar, pero no pasan a ser propiedad del supervisor.

`qa:stop` detiene únicamente los procesos que inició ese supervisor. Primero solicita el cierre autenticado del backend, cancela/termina su trabajo según las reglas existentes y espera informes/correo; después cierra sus procesos propios. MySQL y servicios reutilizados o ajenos se conservan. Si se agota la espera de cierre, se detienen los procesos propios y los trabajos incompletos conservan sus estados recuperables; no se convierten en PASS.

El secreto de control se crea en `.runtime/stack-{SID-del-usuario}.json` en Windows (y `.runtime/stack.json` en otros sistemas), protegido con ACL del usuario actual y SYSTEM mediante herramientas nativas de Windows. Esto mantiene separados los estados heredados de otra cuenta de Windows, que pueden tener ACL incompatibles. No se modifica la política de ejecución de PowerShell ni se guarda el secreto en la web. Los estados anteriores se conservan en `.runtime`; no compartir ese directorio. Si un proceso antiguo sigue vivo pero su control no puede acreditarse, el lanzador se niega a detenerlo o sustituirlo automáticamente.

## Cobertura de los 37 casos

Se incorporaron veinte escenarios trazados a los casos que estaban pendientes o parciales. Diecinueve pueden validarse completamente con rutas/controladores reales y migraciones originales en SQLite en memoria, sin conectar a MySQL. El vigésimo, TC-34, ejecuta controles parciales y declara BLOCKED porque falta una referencia de vídeo autorizada para verificar MediaPipe.

Cobertura implementada: 36 completos y 1 parcial/bloqueado, sin casos sin automatización. «Completo» describe el escenario implementado, no un PASS nuevo de todos los casos ni equivalencia entre SQLite y MySQL. Las diferencias de motores documentadas en MYSQL_SQLITE.md siguen vigentes.

Decisiones confirmadas por el usuario:

- TC-06: registro crea postulación pendiente, sin crear directamente una cuenta.
- TC-37: máximo de correo 50 caracteres. Se prueban 29, 30, 31, 49, 50 y 51.
- TC-34: no existe vídeo/oráculo autorizado; documentar bloqueo y no declararlo completo.

Los nombres originales y criterios anteriores de TC-06/37 se conservan como `originalName` y `acceptanceHistory`; sus IDs y el documento fuente original se mantienen. La matriz completa está en COBERTURA_37_VIGENTE.md.

## Escenarios y oráculos implementados

### Ampliación transversal del sistema

El catálogo suma 127 casos QA-EXT/QA-DEEP bajo una suite separada. Además de las vistas y permisos por módulo, los casos profundos cubren postulaciones, sesiones, evaluaciones clínicas, SEPI, planes de rehabilitación, capturas de video, propuestas FIG, el motor experto, roles y revisión ML.

Son pruebas de integración del HTTP kernel Laravel, con migraciones reales sobre SQLite en memoria y almacenamiento temporal. No modifican la aplicación productiva ni conectan a MySQL. Se pueden seleccionar por categoría o por ID QA-EXT-*; el catálogo usa etiquetas para buscar por módulo y objetivo. El flujo de postulaciones es transaccional y solo cambia la base temporal.

La expansión suma 127 casos de módulos, de los cuales 104 son escenarios profundos, y lleva el catálogo de 44 a 171 ejecutables. La trazabilidad original continúa en 36/37 completos; los nuevos casos amplían módulos y permisos, pero no se usan para declarar completo el oráculo humano de MediaPipe. La lista detallada y límites de cobertura están en AMPLIACION_COBERTURA_SISTEMA.md.

### Casos del plan original y oráculos

- TC-01: redirección anónima real.
- TC-06/07/08/09/37: postulación, tildes, edad inferior, identidad duplicada y fronteras de correo; siempre se verifica persistencia o ausencia de inserción.
- TC-15: auditoría accesible a administrador y rechazada al gimnasta.
- TC-18: PDF individual con cabecera, estructura PDF y contenido descomprimido del atleta correcto; contraste de permisos.
- TC-20/22: portal real de psicología y rechazo del chat anónimo con token CSRF de sesión, sin llamadas al proveedor.
- TC-25..30: fecha pasada, sesión válida, solapamiento y límites de horario/duración; controles de filas y duración.
- TC-31: rechazo PHP en `/analisis/upload`, sin archivos ni filas, con WebM válido como control positivo. No se confunde con `/juez/store`.
- TC-35: catálogo pequeño con grafo de conexiones; referencia independiente por enumeración exhaustiva de caminos, restricciones y puntuación conocida, comparada con la ruta HTTP real.
- TC-36: dos alternativas de rehabilitación con costo independiente 6 frente a 13 días, sin penalización de riesgo; se verifica consulta sin guardar, selección, guardado, calendario y fases.
- TC-34: vista real con controles de vídeo/MediaPipe, puntuación calculada en el servidor, cierre y rechazo de una segunda modificación. La inferencia con vídeo humano y su referencia NO se ha validado; el resultado final es BLOCKED/NOT_RUN, sin skip.

Los dos oráculos de planificación cubren los fixtures descritos; no son una prueba matemática de optimalidad para todos los catálogos o configuraciones. No se ajustaron reglas de negocio para conseguir aprobaciones.

## Ejecución y evidencias

```powershell
npm run qa:test-plan
npm run qa:catalog
npm run qa:test-backend
```

La ampliación está en `tests/plan/plan.spec.ts` y `tests/backend/QaPlanTest.php`, junto a la arquitectura existente. Cada método usa una aplicación nueva, esquema real migrado solo en memoria, claves foráneas activas antes y después de migrar, almacenamiento temporal propio, fecha controlada y solicitudes externas prohibidas. La contraseña de los fixtures se genera aleatoriamente y no se fija en el código.

Las suites funcionales y no funcionales suman 165 casos seleccionables: 18 existentes, veinte ligados al plan original y 127 de expansión. Se selecciona un tipo por ejecución. Los escenarios PHPUnit se ejecutan con Laravel y SQLite, aunque el runner permita asociarlos a proyectos de navegador; para ellos Chromium/Firefox/WebKit no cambia el motor de prueba. El recuento del catálogo describe disponibilidad, no resultados PASS.

El catálogo total tiene 171 ejecutables: 165 de las suites de producto y seguridad, una validación suplementaria MySQL, tres perfiles HTTP y dos demostraciones controladas. Los informes y correos siguen generándose con estados reales. Los veinte casos del plan original conservan el resultado esperado de 19 PASS y 1 NOT_RUN por TC-34; la nueva suite ampliada es independiente de ese resultado.

`node scripts/validate-plan-and-start.cjs` verifica arranque idempotente, panel autenticado, ejecución de veinte casos, informes, entrega local y móvil. Deben estar iniciados `qa:start` y el correo local. Cada corrida conserva sus propios resultados y evidencias.


La comprobación acepta `--resume ID` para volver a revisar una ejecución ya guardada sin repetir pruebas ni correos. Las lecturas de validación admiten hasta dos reintentos exclusivamente ante ECONNRESET; los POST, estados HTTP y aserciones no se reintentan.
