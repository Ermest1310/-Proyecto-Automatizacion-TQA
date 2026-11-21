# Interfaz web local — fases 5 y 6

## Arranque

Sobre `E:\qa`, con MySQL de Laragon iniciado y la configuración privada existente, abrir dos terminales:

```powershell
cd E:\qa
npm run qa:app
```

```powershell
cd E:\qa
npm run qa:backend
```

Abrir `http://127.0.0.1:9876/`. Introducir el valor de `QA_API_TOKEN` del archivo privado `.env.local`. No copiar este archivo a entregables. La UI y la API comparten el mismo servidor; no hay publicación externa ni dependencias gráficas remotas.

## Operación

1. Escribir un único correo válido. Se valida en navegador y servidor (longitud, dominio, sintaxis y rechazo de inyección de encabezados). La validación no acredita la existencia del buzón. El correo queda en la selección y el historial; PDF y HTML se envían al finalizar, con estado de entrega separado.
2. Elegir tipo y categoría. Solo se ofrecen casos implementados. La categoría completa excluye la validación suplementaria MySQL, que se selecciona explícitamente y se ejecuta por separado.
3. Para casos concretos, elegir «Casos seleccionados», buscar y marcar. Cambiar de tipo o categoría limpia la selección para impedir ejecución de casos ocultos.
4. Elegir uno o varios navegadores. Los perfiles no funcionales de rendimiento/carga usan proyecto HTTP; se oculta la selección de navegadores. El servidor rechaza mezclas entre configuraciones.
5. Ejecutar. El progreso refleja casos finalizados / previstos, no un porcentaje de tiempo estimado. Se muestran estado, identificador, inicio, duración, PASS/FAIL/NOT_RUN/SKIPPED, errores y métricas cuando corresponden.
6. Consultar el historial o cancelar una corrida propia del ejecutor. Cada carpeta de resultados conserva su identidad. La interfaz consulta los últimos cien registros; los anteriores permanecen en disco.

## Sesión y acceso

Cookie opaca HttpOnly y SameSite=Strict, duración de seis horas; el token de configuración no se incrusta en HTML/JS ni se guarda en localStorage/sessionStorage. Las escrituras con cookie requieren cabecera CSRF. La API Bearer anterior permanece compatible. Cerrar sesión invalida la cookie. Reiniciar el servidor invalida sesiones en memoria y requiere entrar nuevamente. Tras diez accesos inválidos, se limita el acceso cinco minutos por IP. Los archivos privados y evidencias no se sirven como directorio estático.

El servidor escucha únicamente en 127.0.0.1. Está diseñado para uso local individual. No exponer este servidor HTTP en una red ni publicarlo sin diseñar autenticación, HTTPS y controles de despliegue específicos.

## Validación reproducible de la interfaz

```powershell
node scripts/validate-web.cjs
```

El script abre Chromium, verifica sesión, correo inválido, categorías y casos, progreso, métricas, historial y móvil de 390 px; genera capturas y JSON en una nueva carpeta `reports/web-validation-*`. Ejecuta 64 lecturas de /login entre los tres perfiles y un caso BUG-002 en SQLite aislado. El resultado funcional de los perfiles puede ser PASS o FAIL: ambos deben verse correctamente; un error de infraestructura sí impide completar esta validación. `--resume` reutiliza las últimas mediciones performance/load existentes para revisar la presentación sin repetir carga; el JSON conserva sus IDs históricos.

La suite por defecto mantiene los 18 casos × 3 navegadores. El catálogo tiene 24 ejecutables: 18 de esa suite, 1 suplementario MySQL, 3 perfiles HTTP y 2 demostraciones separadas. No se modifica la cobertura declarada de los 37 casos originales.


## Informes disponibles - fases 7 y 8

Al finalizar, el panel permite descargar PDF y HTML independiente. Para ejecuciones anteriores, usar «Generar PDF y HTML». La generación tiene su propio estado y no modifica los resultados de pruebas. Consultar INFORMES_EXPORTABLES.md para estructura, privacidad y uso por CLI/API.


## Correo automático - fases 9 y 10

PDF y HTML se envían después de generar ambos documentos. El panel muestra entrega y permite descargar adjuntos realmente recibidos en el buzón local. Use demo@localhost.test en modo local. La aceptación SMTP externa no se presenta como recepción confirmada. El catálogo vigente agrega dos demostraciones, total 24; las cifras de 22 anteriores corresponden a la fase 6. Consultar CORREO_Y_DEMOSTRACION.md para configuración, errores y prueba integral.
