# Decisiones de la evolución QA

## Límites de confianza

El código fuente de las pruebas y sus bindings constituyen la superficie ejecutable autorizada. La edición desde la web modifica metadatos, nunca comandos, JavaScript, rutas de archivos ni variables de proceso. Un caso nuevo sin binding implementado conserva el estado de automatización pendiente; no puede producir un PASS por existir en el catálogo.

La plataforma permanece en loopback. La sesión y CSRF existentes protegen las operaciones web. Un token compartido acredita acceso local, no la identidad individual de una persona: un autor indicado en un cambio es una atribución declarada, no una firma de identidad.

## Persistencia y trazabilidad

Se reutilizan las carpetas de resultados existentes. Los datos de plataforma se separan de los resultados originales y de Laravel. Los registros versionados deben conservar revisiones, autor, fecha, motivo y diff; las modificaciones usan control de concurrencia para evitar que una edición reemplace inadvertidamente otra. El archivado es lógico y preserva el historial.

Los hashes sirven para detectar alteraciones respecto de una referencia guardada. Sin una firma y un anclaje externos no constituyen prueba contra un administrador con control completo de todos los archivos.

Los resultados guardan la selección vigente al ejecutar. Una edición posterior del catálogo no debe reescribir la definición histórica. La eliminación o el cambio de alcance de una selección no se confunde con una corrección del producto.

## Ejecución segura

El ejecutor existente sigue siendo el único que inicia Playwright. No se usa shell ni se aceptan comandos suministrados desde la web o CI. Los límites de duración, salida, carga y proyectos continúan activos.

El paralelismo requiere aislamiento demostrable. Los escenarios que comparten MySQL o almacenamiento de la aplicación deben permanecer serializados; las pruebas aisladas pueden aprovechar workers separados con archivos, semilla y directorios propios. La prioridad nunca debe omitir dependencias ni permitir sobrepasar límites.

Una reproducción solo es equivalente cuando coinciden código, configuración relevante, dependencias y datos. Los servicios externos, el navegador y las condiciones de tiempo introducen límites que deben figurar en el snapshot. La plataforma no debe prometer reproducción exacta cuando falte procedencia verificable.

## Calidad de los resultados

Un bloqueo, una ausencia de resultado o un error de infraestructura no se convierten en aprobación. Las demostraciones controladas permanecen fuera de la evaluación del producto. Un PASS aislado no cierra automáticamente un defecto.

La preparación de entrega debe explicar las reglas y las referencias que produjeron su decisión. Los asistentes de diagnóstico deben citar resultados y evidencias, separar observaciones de hipótesis y declarar si utilizan reglas locales o un proveedor de IA. Los datos privados no deben salir del equipo de forma implícita.

Las mutaciones se ejecutan exclusivamente en copias aisladas del código QA. Sus resultados describen qué controles fueron sometidos a alteraciones y cuáles detectaron el cambio; no constituyen cobertura de mutación de todo Laravel.
