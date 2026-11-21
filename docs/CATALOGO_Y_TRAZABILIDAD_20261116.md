# Catálogo y trazabilidad de pruebas — 16/11/2026

Fuente canónica: tabla 3.2 de E:\laragon\www\Proyecto2\docs\entregable1_pruebas_funcionales.md. Se conservaron los 37 IDs, los títulos y resultados históricos. No son 37 pruebas ejecutadas hoy.

## Resumen

{
  "originalCases": 37,
  "defaultAutomatedTests": 18,
  "explicitSupplementaryTests": 1,
  "coverage": {
    "pending": 18,
    "complete": 17,
    "partial": 2
  }
}

Completa significa que las aserciones cubren el escenario actual indicado; no certifica todos los motores ni todo MySQL. Parcial indica diferencia de criterio o controles faltantes. Pendiente no tiene automatización actual equivalente.

## Discrepancias y decisiones

- IDs representativos vs tabla 3.2
- TC-06 ahora genera postulación pendiente, no cuenta directa
- TC-22 anónimo no está cubierto por prueba de errores autenticada BUG-006
- TC-37 máximo histórico 30 vs actual 50
- TSV de rondas contiene estados parciales/contradictorios: no usarlo como resultado nuevo

BUG-002 mantiene el criterio aprobado de aceptar &: TC-10 canónico / TC-03 representativo. Otros aliases: BUG-004 TC-17/ejemplo TC-06; BUG-005 TC-19/ejemplo TC-07; BUG-008 TC-32/ejemplo TC-11. Los encabezados históricos de los tests se conservan como títulos, no como fuente de numeración canónica.

## Matriz de los 37 casos

| ID | Escenario original | Tipo / categoría | Cobertura | Automatización | Histórico reportado | Observación |
|---|---|---|---|---|---|---|
| TC-01 | GET / anónimo → redirección a /login | functional / smoke | pending | Pendiente | PASS | — |
| TC-02 | GET /login formulario disponible | functional / smoke | complete | QA-AUTH-01, QA-API-02 | PASS | — |
| TC-03 | Login con email+nacionalidad+cédula real pero password incorrecta | functional / smoke | complete | QA-API-01 | PASS | QA-AUTH-02 usa un usuario inexistente: es complementario, no reemplaza esta precondición. |
| TC-04 | Login completo de Administrador (admin.inicio) → sesión admin establecida | functional / smoke | complete | QA-AUTH-01, QA-FUNC-01, QA-REG-01, QA-INT-01 | PASS | — |
| TC-05 | Login completo de Gimnasta → GET /gimnasta/dashboard 200 | functional / smoke | complete | QA-AUTHZ-01, QA-SEC-003 | PASS | La comprobación se ejecuta como precondición de autorización, no como prueba UI completa de ese panel. |
| TC-06 | Registro de atleta con datos válidos → fila creada con rol Gimnasta | functional / smoke | partial | QA-SEC-002, QA-REG-002-LIVE | PASS | Cambio de flujo: el sistema actual requiere aprobación. No equivale a crear directamente una cuenta con rol Gimnasta; actualizar aceptación de forma explícita. |
| TC-07 | Registro con nombre "María González" + tilde → íntegro en BD | functional / boundary | pending | Pendiente | PASS | — |
| TC-08 | Registro con edad 9 (límite inferior −1) | functional / boundary | pending | Pendiente | PASS | — |
| TC-09 | Registro con cédula duplicada (QA-001 ya existente) | functional / boundary | pending | Pendiente | PASS | — |
| TC-10 | Registro apellido "Sotomayor & Vargas" | functional / boundary | complete | QA-SEC-002, QA-REG-002-LIVE | FAIL | Alias representativo TC-03 del apartado 2 y de la conversación; el ID canónico de tabla 3.2 es TC-10. |
| TC-11 | Registre anónimo ejecuta el diagnóstico de postulaciones (`/test_postulaciones.php`) | nonfunctional / security | complete | QA-SEC-001 | FAIL | 404 es admisible para el archivo original retirado, no para cualquier ruta inventada. |
| TC-12 | GIMNASTA accede `/admin/reportes` | nonfunctional / security | complete | QA-SEC-003, QA-AUTHZ-01 | FAIL | — |
| TC-13 | GIMNASTA descarga PDF completo (`/admin/reportes/exportar-pdf`) | nonfunctional / security | complete | QA-SEC-003 | FAIL | — |
| TC-14 | GIMNASTA accede `/juez/dashboard` | nonfunctional / security | complete | QA-SEC-003 | FAIL | — |
| TC-15 | GIMNASTA accede `/admin/audit` | nonfunctional / security | pending | Pendiente | PASS | — |
| TC-16 | Admin descarga backup válido (nombre correcto) → SQL entregado | functional / integration | complete | QA-SEC-004 | PASS | Control de descarga en kernel real con storage aislado; no certifica creación/restauración de backups MySQL completos. |
| TC-17 | Path traversal `..\..\..\.env` en `descargarBackup` | nonfunctional / security | complete | QA-SEC-004 | FAIL | Alias representativo TC-06; canonical TC-17. Centinela fuera del directorio en storage de pruebas. |
| TC-18 | Admin exporta PDF individual de gimnasta (FPDF) | functional / integration | pending | Pendiente | PASS | — |
| TC-19 | GIMNASTA aprueba solicitud de nivel ID=2 | nonfunctional / security | complete | QA-SEC-005 | FAIL | Alias representativo TC-07. La ruta histórica entrenador fue retirada; se verifica la ruta actual admin, sin aceptar 404 como autorización. |
| TC-20 | GET /areamedica/psicologia → 200 | functional / smoke | pending | Pendiente | PASS | Abrir /areamedica/psicologia/chatbot no es lo mismo que este portal. |
| TC-21 | Botón chatbot visible + fetch a `/api/psicologia/chat` presente | functional / smoke | complete | QA-SEC-006 | PASS | — |
| TC-22 | POST `/api/psicologia/chat` anónimo | nonfunctional / security | partial | QA-SEC-006 | FAIL | El test actual comprueba errores del servicio con admin y proveedor simulado, pero no envía chat anónimo. TC-22 y la regresión de errores BUG-006 no son idénticos. |
| TC-23 | POST `/api/ia/predecir-deduccion` anónimo | nonfunctional / security | complete | QA-API-02 | FAIL | — |
| TC-24 | POST `/api/ia/registrar-feedback` anónimo | nonfunctional / security | complete | QA-API-02 | FAIL | La prueba actual verifica el rechazo; una comprobación adicional de no escritura sería útil al ampliar el caso. |
| TC-25 | POST /sesiones con Fecha=2020 (pasada) | functional / boundary | pending | Pendiente | PASS | — |
| TC-26 | POST /sesiones válida 10:00–11:00 futura → fila creada (BD) | functional / integration | pending | Pendiente | PASS | — |
| TC-27 | POST /sesiones solapado 10:30–11:30 (conflicto mismo gimnasta) | functional / boundary | pending | Pendiente | PASS | — |
| TC-28 | POST /sesiones Hora_Inicio=05:59 | functional / boundary | pending | Pendiente | PASS | Alias representativo TC-08. |
| TC-29 | POST /sesiones duración 20 min (< 30) | functional / boundary | pending | Pendiente | PASS | — |
| TC-30 | POST /sesiones duración 175 min (48 bajo el máx 180) | functional / boundary | pending | Pendiente | PASS | Alias representativo TC-09; no confundir con duplicidad de cédula del TC-09 canónico. |
| TC-31 | POST `/analisis/upload` archivo `.php` → rechazado por mimes | nonfunctional / security | pending | Pendiente | PASS | Alias representativo TC-10. Probar /juez/store (BUG-008) no cubre este endpoint distinto. |
| TC-32 | POST `/juez/store` con archivo `.php` como video | nonfunctional / security | complete | QA-SEC-008 | FAIL | Alias representativo TC-11; canonical TC-32. |
| TC-33 | POST /logout con sesión vigente → 302 a login | functional / integration | complete | QA-FUNC-02 | PASS | — |
| TC-34 | Evaluación completa de juez con MediaPipe en navegador | functional / e2e | pending | Pendiente | NOT RUN | Histórico NOT RUN. Acceder al panel de juez no cubre evaluación completa. Falta concretar oráculo y datos. |
| TC-35 | Rutinas óptimas NBA* (`POST /rutinas/planificar-optima`) | functional / integration | pending | Pendiente | NOT RUN | Histórico NOT RUN; se requiere fixture y oráculo de algoritmo, no inventar respuesta esperada. |
| TC-36 | Planificación de rehabilitación A* | functional / integration | pending | Pendiente | NOT RUN | Histórico NOT RUN; se debe completar fixture y oráculo antes de automatizar. |
| TC-37 | Registro con email superto 30 chars (max validation del código) | functional / boundary | pending | Pendiente | NOT RUN | No cambiar silenciosamente la expectativa: límite original ambiguo frente a implementación actual de 50. |

## Fichas detalladas de los 37 casos

### TC-01 — GET / anónimo → redirección a /login

**Objetivo:** Verificar: GET / anónimo → redirección a /login

**Tipo/categoría:** functional / smoke. **Ruta:** GET /

**Precondiciones:** Visitante sin cookies de sesión; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Sin credenciales

**Pasos:**

1. Crear contexto sin sesión
2. Solicitar / sin seguir redirecciones
3. Comprobar Location y estado

**Esperado:** Redirección a /login; no entregar panel protegido

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-02 — GET /login formulario disponible

**Objetivo:** Verificar: GET /login formulario disponible

**Tipo/categoría:** functional / smoke. **Ruta:** GET /login

**Precondiciones:** Servidor QA disponible; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Sin sesión

**Pasos:**

1. Abrir /login
2. Comprobar formulario y campos de acceso

**Esperado:** 200; formulario de login utilizable

**Cobertura:** complete; **pruebas:** QA-AUTH-01, QA-API-02. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-03 — Login con email+nacionalidad+cédula real pero password incorrecta

**Objetivo:** Verificar: Login con email+nacionalidad+cédula real pero password incorrecta

**Tipo/categoría:** functional / smoke. **Ruta:** POST /login

**Precondiciones:** Cuenta QA existente y activa; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Email/nacionalidad/documento de cuenta QA; contraseña deliberadamente incorrecta

**Pasos:**

1. Obtener CSRF
2. Enviar credencial incorrecta
3. Comprobar error y ausencia de sesión

**Esperado:** Rechazo genérico de credenciales; no acceso al panel

**Cobertura:** complete; **pruebas:** QA-API-01. **Observación:** QA-AUTH-02 usa un usuario inexistente: es complementario, no reemplaza esta precondición.

### TC-04 — Login completo de Administrador (admin.inicio) → sesión admin establecida

**Objetivo:** Verificar: Login completo de Administrador (admin.inicio) → sesión admin establecida

**Tipo/categoría:** functional / smoke. **Ruta:** POST /login → GET /admin/inicio

**Precondiciones:** Administrador QA activo; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Credenciales por referencia a TEST_CREDENTIALS.admin, sin publicar sus valores

**Pasos:**

1. Abrir login
2. Completar credenciales de administrador
3. Enviar
4. Comprobar panel y recurso protegido

**Esperado:** Sesión de administrador y panel autorizado disponibles

**Cobertura:** complete; **pruebas:** QA-AUTH-01, QA-FUNC-01, QA-REG-01, QA-INT-01. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-05 — Login completo de Gimnasta → GET /gimnasta/dashboard 200

**Objetivo:** Verificar: Login completo de Gimnasta → GET /gimnasta/dashboard 200

**Tipo/categoría:** functional / smoke. **Ruta:** POST /login → GET /gimnasta/dashboard

**Precondiciones:** Gimnasta QA activo MAG; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Referencia a TEST_CREDENTIALS.athlete

**Pasos:**

1. Iniciar sesión gimnasta
2. Solicitar su panel con la misma sesión
3. Comprobar respuesta

**Esperado:** Panel de gimnasta 200 con sesión efectiva

**Cobertura:** complete; **pruebas:** QA-AUTHZ-01, QA-SEC-003. **Observación:** La comprobación se ejecuta como precondición de autorización, no como prueba UI completa de ese panel.

### TC-06 — Registro de atleta con datos válidos → fila creada con rol Gimnasta

**Objetivo:** Verificar: Registro de atleta con datos válidos → fila creada con rol Gimnasta

**Tipo/categoría:** functional / smoke. **Ruta:** POST /register

**Precondiciones:** Datos válidos nuevos en BD QA; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Nombre Pedro, apellido normal, identidad sintética única y atributos MAG válidos

**Pasos:**

1. Obtener CSRF
2. Enviar registro válido
3. Comprobar redirección
4. Consultar persistencia y rol/estado resultante

**Esperado:** Original: cuenta Gimnasta creada. Actual: postulación pendiente creada íntegra

**Cobertura:** partial; **pruebas:** QA-SEC-002, QA-REG-002-LIVE. **Observación:** Cambio de flujo: el sistema actual requiere aprobación. No equivale a crear directamente una cuenta con rol Gimnasta; actualizar aceptación de forma explícita.

### TC-07 — Registro con nombre "María González" + tilde → íntegro en BD

**Objetivo:** Verificar: Registro con nombre "María González" + tilde → íntegro en BD

**Tipo/categoría:** functional / boundary. **Ruta:** POST /register

**Precondiciones:** Datos nuevos válidos; campo con tildes permitido; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Nombre/apellido de prueba con María y González; sin datos personales reales

**Pasos:**

1. Enviar registro con tildes
2. Consultar fila persistida
3. Comparar texto sin pérdida de caracteres

**Esperado:** Nombres con tildes conservados íntegramente

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-08 — Registro con edad 9 (límite inferior −1)

**Objetivo:** Verificar: Registro con edad 9 (límite inferior −1)

**Tipo/categoría:** functional / boundary. **Ruta:** POST /register

**Precondiciones:** Datos restantes válidos; sin identidad duplicada; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Fecha de nacimiento calculada para exactamente 9 años

**Pasos:**

1. Enviar registro con edad 9
2. Comprobar error de edad
3. Verificar que no se creó postulación

**Esperado:** Rechazo por edad inferior a 10; sin persistencia

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-09 — Registro con cédula duplicada (QA-001 ya existente)

**Objetivo:** Verificar: Registro con cédula duplicada (QA-001 ya existente)

**Tipo/categoría:** functional / boundary. **Ruta:** POST /register

**Precondiciones:** Identidad sintética previamente registrada en fixture QA; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Misma CedulaIdentidad existente; email nuevo

**Pasos:**

1. Preparar identidad existente
2. Enviar registro duplicado
3. Comprobar error del campo y conteo

**Esperado:** Rechazo por identidad duplicada; sin segunda fila

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-10 — Registro apellido "Sotomayor & Vargas"

**Objetivo:** Verificar: Registro apellido "Sotomayor & Vargas"

**Tipo/categoría:** functional / boundary. **Ruta:** POST /register

**Precondiciones:** Datos válidos nuevos y control sin ampersand; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Apellido Sotomayor & Vargas; control Sotomayor Vargas

**Pasos:**

1. Registrar control normal
2. Registrar apellido con & desde formulario real
3. Comparar texto persistido
4. Rechazar caracteres no permitidos sin crear filas

**Esperado:** Ampersand aceptado en frontend/backend y conservado íntegro; entradas no permitidas rechazadas

**Cobertura:** complete; **pruebas:** QA-SEC-002, QA-REG-002-LIVE. **Observación:** Alias representativo TC-03 del apartado 2 y de la conversación; el ID canónico de tabla 3.2 es TC-10.

### TC-11 — Registre anónimo ejecuta el diagnóstico de postulaciones (`/test_postulaciones.php`)

**Objetivo:** Verificar: Registre anónimo ejecuta el diagnóstico de postulaciones (`/test_postulaciones.php`)

**Tipo/categoría:** nonfunctional / security. **Ruta:** GET /test_postulaciones.php

**Precondiciones:** Visitante sin sesión; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Ruta original del script de diagnóstico

**Pasos:**

1. Solicitar script sin sesión
2. Inspeccionar estado y ausencia de PII/configuración

**Esperado:** Archivo retirado o protegido; no exponer postulaciones

**Cobertura:** complete; **pruebas:** QA-SEC-001. **Observación:** 404 es admisible para el archivo original retirado, no para cualquier ruta inventada.

### TC-12 — GIMNASTA accede `/admin/reportes`

**Objetivo:** Verificar: GIMNASTA accede `/admin/reportes`

**Tipo/categoría:** nonfunctional / security. **Ruta:** GET /admin/reportes

**Precondiciones:** Sesión confirmada de gimnasta; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Rol Gimnasta

**Pasos:**

1. Autenticar gimnasta
2. Solicitar reportes admin
3. Comprobar denegación del servidor

**Esperado:** 403 y ausencia de datos administrativos

**Cobertura:** complete; **pruebas:** QA-SEC-003, QA-AUTHZ-01. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-13 — GIMNASTA descarga PDF completo (`/admin/reportes/exportar-pdf`)

**Objetivo:** Verificar: GIMNASTA descarga PDF completo (`/admin/reportes/exportar-pdf`)

**Tipo/categoría:** nonfunctional / security. **Ruta:** GET /admin/reportes/exportar-pdf

**Precondiciones:** Sesión confirmada de gimnasta; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Rol Gimnasta

**Pasos:**

1. Autenticar gimnasta
2. Solicitar PDF administrativo
3. Comprobar denegación y no entrega de PDF

**Esperado:** 403; no descargar PDF administrativo

**Cobertura:** complete; **pruebas:** QA-SEC-003. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-14 — GIMNASTA accede `/juez/dashboard`

**Objetivo:** Verificar: GIMNASTA accede `/juez/dashboard`

**Tipo/categoría:** nonfunctional / security. **Ruta:** GET /juez/dashboard

**Precondiciones:** Sesión confirmada de gimnasta; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Rol Gimnasta

**Pasos:**

1. Autenticar gimnasta
2. Solicitar panel juez
3. Comprobar denegación

**Esperado:** 403; no obtener contenido del juez

**Cobertura:** complete; **pruebas:** QA-SEC-003. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-15 — GIMNASTA accede `/admin/audit`

**Objetivo:** Verificar: GIMNASTA accede `/admin/audit`

**Tipo/categoría:** nonfunctional / security. **Ruta:** GET /admin/audit

**Precondiciones:** Sesión confirmada de gimnasta; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Rol Gimnasta

**Pasos:**

1. Autenticar gimnasta
2. Solicitar auditoría admin
3. Comprobar estado y contenido

**Esperado:** 403 sin datos de auditoría

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-16 — Admin descarga backup válido (nombre correcto) → SQL entregado

**Objetivo:** Verificar: Admin descarga backup válido (nombre correcto) → SQL entregado

**Tipo/categoría:** functional / integration. **Ruta:** GET /admin/reportes/descargar-backup/{filename}

**Precondiciones:** Administrador y backup de prueba controlado existente; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** qa_control.sql dentro del almacenamiento aislado

**Pasos:**

1. Crear archivo SQL centinela seguro
2. Autenticar admin
3. Descargar nombre válido
4. Comparar archivo recibido

**Esperado:** 200 con descarga del SQL esperado

**Cobertura:** complete; **pruebas:** QA-SEC-004. **Observación:** Control de descarga en kernel real con storage aislado; no certifica creación/restauración de backups MySQL completos.

### TC-17 — Path traversal `..\..\..\.env` en `descargarBackup`

**Objetivo:** Verificar: Path traversal `..\..\..\.env` en `descargarBackup`

**Tipo/categoría:** nonfunctional / security. **Ruta:** GET /admin/reportes/descargar-backup/..%5C..%5C..%5C.env

**Precondiciones:** Admin autenticado; control de descarga válida; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Vector original y centinela fuera de backups; sin usar secretos reales

**Pasos:**

1. Confirmar acceso autorizado
2. Descargar backup válido de control
3. Enviar traversal
4. Comprobar no entrega de centinela/secretos

**Esperado:** 404 de validación; sin descarga ni contenido externo

**Cobertura:** complete; **pruebas:** QA-SEC-004. **Observación:** Alias representativo TC-06; canonical TC-17. Centinela fuera del directorio en storage de pruebas.

### TC-18 — Admin exporta PDF individual de gimnasta (FPDF)

**Objetivo:** Verificar: Admin exporta PDF individual de gimnasta (FPDF)

**Tipo/categoría:** functional / integration. **Ruta:** GET /admin/reportes/exportar-gimnasta/{id}

**Precondiciones:** Admin y gimnasta de prueba existente; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** ID de gimnasta controlado, por resolver en fixture

**Pasos:**

1. Autenticar admin
2. Solicitar PDF individual
3. Verificar contenido/tipo y pertenencia al atleta

**Esperado:** PDF válido del gimnasta solicitado

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-19 — GIMNASTA aprueba solicitud de nivel ID=2

**Objetivo:** Verificar: GIMNASTA aprueba solicitud de nivel ID=2

**Tipo/categoría:** nonfunctional / security. **Ruta:** POST /admin/solicitudes-nivel/{id}/aprobar

**Precondiciones:** Solicitud ajena pendiente y dos atletas válidos; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Atacante Gimnasta 2, víctima 7 y administrador 1 en SQLite en memoria

**Pasos:**

1. Tomar snapshot completo de solicitud y niveles
2. Enviar aprobación como gimnasta
3. Comparar estado sin cambios
4. Aprobar la misma solicitud como admin y verificar control positivo

**Esperado:** Gimnasta 403 y sin cambios; administrador puede aprobar la fixture válida

**Cobertura:** complete; **pruebas:** QA-SEC-005. **Observación:** Alias representativo TC-07. La ruta histórica entrenador fue retirada; se verifica la ruta actual admin, sin aceptar 404 como autorización.

### TC-20 — GET /areamedica/psicologia → 200

**Objetivo:** Verificar: GET /areamedica/psicologia → 200

**Tipo/categoría:** functional / smoke. **Ruta:** GET /areamedica/psicologia

**Precondiciones:** Sesión con permission:areamedica.view; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Administrador o profesional médico QA autorizado

**Pasos:**

1. Autenticar rol permitido
2. Solicitar portal psicología
3. Comprobar 200 y contenido del portal

**Esperado:** Portal de psicología 200 y contenido esperado

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Abrir /areamedica/psicologia/chatbot no es lo mismo que este portal.

### TC-21 — Botón chatbot visible + fetch a `/api/psicologia/chat` presente

**Objetivo:** Verificar: Botón chatbot visible + fetch a `/api/psicologia/chat` presente

**Tipo/categoría:** functional / smoke. **Ruta:** GET /areamedica/psicologia/chatbot → POST /api/psicologia/chat

**Precondiciones:** Usuario permitido; frontend y dependencias cargados; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Mensaje de concentración deportiva sin datos personales

**Pasos:**

1. Abrir chatbot
2. Comprobar input y botón reales
3. Enviar mensaje
4. Esperar respuesta del endpoint y verla en mensajes

**Esperado:** Controles disponibles y petición real vinculada a respuesta visible

**Cobertura:** complete; **pruebas:** QA-SEC-006. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-22 — POST `/api/psicologia/chat` anónimo

**Objetivo:** Verificar: POST `/api/psicologia/chat` anónimo

**Tipo/categoría:** nonfunctional / security. **Ruta:** POST /api/psicologia/chat sin sesión

**Precondiciones:** Visitante, CSRF válido para no fallar por 419; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Mensaje seguro; sin cookies de autenticación

**Pasos:**

1. Obtener sesión anónima y CSRF
2. Enviar mensaje al endpoint
3. Comprobar rechazo de autenticación y no exposición interna

**Esperado:** 401 de autenticación; sin trazas/secretos ni consumo autorizado

**Cobertura:** partial; **pruebas:** QA-SEC-006. **Observación:** El test actual comprueba errores del servicio con admin y proveedor simulado, pero no envía chat anónimo. TC-22 y la regresión de errores BUG-006 no son idénticos.

### TC-23 — POST `/api/ia/predecir-deduccion` anónimo

**Objetivo:** Verificar: POST `/api/ia/predecir-deduccion` anónimo

**Tipo/categoría:** nonfunctional / security. **Ruta:** POST /api/ia/predecir-deduccion

**Precondiciones:** Sin sesión; CSRF válido; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Payload JSON; el control de autenticación debe preceder la validación

**Pasos:**

1. Obtener CSRF anónimo
2. Enviar POST IA
3. Comprobar JSON de autenticación

**Esperado:** 401 JSON; 404/419/422 no demuestran protección

**Cobertura:** complete; **pruebas:** QA-API-02. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-24 — POST `/api/ia/registrar-feedback` anónimo

**Objetivo:** Verificar: POST `/api/ia/registrar-feedback` anónimo

**Tipo/categoría:** nonfunctional / security. **Ruta:** POST /api/ia/registrar-feedback

**Precondiciones:** Sin sesión; CSRF válido; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Payload JSON de prueba

**Pasos:**

1. Obtener CSRF anónimo
2. Enviar feedback sin sesión
3. Comprobar rechazo

**Esperado:** 401 JSON de autenticación; no escritura por anónimo

**Cobertura:** complete; **pruebas:** QA-API-02. **Observación:** La prueba actual verifica el rechazo; una comprobación adicional de no escritura sería útil al ampliar el caso.

### TC-25 — POST /sesiones con Fecha=2020 (pasada)

**Objetivo:** Verificar: POST /sesiones con Fecha=2020 (pasada)

**Tipo/categoría:** functional / boundary. **Ruta:** POST /sesiones

**Precondiciones:** Entrenador/admin autorizado y fixtures compatibles; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Fecha pasada 2020-01-01; demás datos válidos

**Pasos:**

1. Preparar participantes y aparato
2. Enviar fecha pasada
3. Comprobar error y ausencia de fila

**Esperado:** Rechazo por fecha pasada; sin sesión creada

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-26 — POST /sesiones válida 10:00–11:00 futura → fila creada (BD)

**Objetivo:** Verificar: POST /sesiones válida 10:00–11:00 futura → fila creada (BD)

**Tipo/categoría:** functional / integration. **Ruta:** POST /sesiones

**Precondiciones:** Participantes/entrenador/aparato válidos sin conflicto; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Fecha futura; horario 10:00–11:00

**Pasos:**

1. Preparar fixture libre
2. Crear sesión
3. Comprobar respuesta y fila persistida

**Esperado:** Sesión válida persistida con datos y duración correctos

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-27 — POST /sesiones solapado 10:30–11:30 (conflicto mismo gimnasta)

**Objetivo:** Verificar: POST /sesiones solapado 10:30–11:30 (conflicto mismo gimnasta)

**Tipo/categoría:** functional / boundary. **Ruta:** POST /sesiones

**Precondiciones:** Sesión existente 10:00–11:00 para mismo gimnasta; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Nueva sesión 10:30–11:30 en misma fecha

**Pasos:**

1. Crear sesión de control
2. Enviar sesión solapada
3. Comprobar rechazo y conteo

**Esperado:** Rechazo por conflicto; sin segunda sesión

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-28 — POST /sesiones Hora_Inicio=05:59

**Objetivo:** Verificar: POST /sesiones Hora_Inicio=05:59

**Tipo/categoría:** functional / boundary. **Ruta:** POST /sesiones

**Precondiciones:** Datos restantes válidos, sin solapamiento; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Inicio 05:59 y fin 06:45 en fecha futura

**Pasos:**

1. Enviar horario anterior a 06:00
2. Comprobar mensaje de horario
3. Verificar ausencia de inserción

**Esperado:** Rechazo por franja fuera de 06:00–22:00

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Alias representativo TC-08.

### TC-29 — POST /sesiones duración 20 min (< 30)

**Objetivo:** Verificar: POST /sesiones duración 20 min (< 30)

**Tipo/categoría:** functional / boundary. **Ruta:** POST /sesiones

**Precondiciones:** Datos restantes válidos; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Horario de 20 minutos dentro de franja permitida

**Pasos:**

1. Enviar duración 20 min
2. Comprobar error de duración
3. Verificar ausencia de fila

**Esperado:** Rechazo por duración inferior a 30 minutos

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-30 — POST /sesiones duración 175 min (48 bajo el máx 180)

**Objetivo:** Verificar: POST /sesiones duración 175 min (48 bajo el máx 180)

**Tipo/categoría:** functional / boundary. **Ruta:** POST /sesiones

**Precondiciones:** Datos restantes válidos; sin conflictos; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Fecha futura, 08:00–10:55, duración 175 min

**Pasos:**

1. Enviar sesión de 175 min
2. Comprobar respuesta
3. Consultar duración y fila persistida

**Esperado:** Aceptación de 175 minutos, inferior al máximo 180

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Alias representativo TC-09; no confundir con duplicidad de cédula del TC-09 canónico.

### TC-31 — POST `/analisis/upload` archivo `.php` → rechazado por mimes

**Objetivo:** Verificar: POST `/analisis/upload` archivo `.php` → rechazado por mimes

**Tipo/categoría:** nonfunctional / security. **Ruta:** POST /analisis/upload

**Precondiciones:** Usuario con videos.create y CSRF válido; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** PHP inerte en campo video

**Pasos:**

1. Tomar conteos BD/archivos
2. Enviar PHP a analisis/upload
3. Comprobar error del archivo y estado sin cambios

**Esperado:** Rechazo por tipo de archivo; no persistencia ni exposición

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Alias representativo TC-10. Probar /juez/store (BUG-008) no cubre este endpoint distinto.

### TC-32 — POST `/juez/store` con archivo `.php` como video

**Objetivo:** Verificar: POST `/juez/store` con archivo `.php` como video

**Tipo/categoría:** nonfunctional / security. **Ruta:** POST /juez/store

**Precondiciones:** Juez, atleta MAG inscrito, competencia y aparato válidos; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** PHP inerte; WebM negro real como control positivo

**Pasos:**

1. Preparar formulario completo y snapshot
2. Enviar PHP
3. Comprobar único error video y conteos iguales
4. Enviar WebM real y comprobar bytes en disco privado

**Esperado:** PHP 422 sin fila/archivo privado o público; WebM válido 200 privado

**Cobertura:** complete; **pruebas:** QA-SEC-008. **Observación:** Alias representativo TC-11; canonical TC-32.

### TC-33 — POST /logout con sesión vigente → 302 a login

**Objetivo:** Verificar: POST /logout con sesión vigente → 302 a login

**Tipo/categoría:** functional / integration. **Ruta:** POST /logout; GET /admin/inicio

**Precondiciones:** Administrador autenticado con menú inicializado; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Sesión QA activa

**Pasos:**

1. Iniciar sesión
2. Abrir menú y pulsar Cerrar Sesión
3. Comprobar login
4. Solicitar recurso protegido sin sesión

**Esperado:** Redirección al login y 401 al recurso protegido

**Cobertura:** complete; **pruebas:** QA-FUNC-02. **Observación:** Sin discrepancia identificada en esta auditoría.

### TC-34 — Evaluación completa de juez con MediaPipe en navegador

**Objetivo:** Verificar: Evaluación completa de juez con MediaPipe en navegador

**Tipo/categoría:** functional / e2e. **Ruta:** GET /juez/evaluar/{id} y flujo MediaPipe

**Precondiciones:** Evaluación real de prueba, video compatible, entorno navegador disponible; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Fixture de evaluación/atleta/competencia y referencias esperadas por definir

**Pasos:**

1. Preparar evaluación
2. Cargar video e inicializar MediaPipe
3. Completar flujo de evaluación
4. Comparar resultado con referencia

**Esperado:** Evaluación completa con evidencia técnica del procesamiento

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Histórico NOT RUN. Acceder al panel de juez no cubre evaluación completa. Falta concretar oráculo y datos.

### TC-35 — Rutinas óptimas NBA* (`POST /rutinas/planificar-optima`)

**Objetivo:** Verificar: Rutinas óptimas NBA* (`POST /rutinas/planificar-optima`)

**Tipo/categoría:** functional / integration. **Ruta:** POST /rutinas/planificar-optima

**Precondiciones:** Sesión autorizada y catálogo de elementos MAG sembrado; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** gimnasta_id, aparato_id y max_elementos 4–12; IDs de fixture por preparar

**Pasos:**

1. Preparar catálogo y referencias
2. Solicitar planificación
3. Comparar rutina y optimalidad/restricciones con oráculo

**Esperado:** Rutina coherente con restricciones y referencia de optimalidad

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Histórico NOT RUN; se requiere fixture y oráculo de algoritmo, no inventar respuesta esperada.

### TC-36 — Planificación de rehabilitación A*

**Objetivo:** Verificar: Planificación de rehabilitación A*

**Tipo/categoría:** functional / integration. **Ruta:** POST /rehabilitacion/crear-plan

**Precondiciones:** Profesional autorizado y lesión propia; fases/catálogo sembrados; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** lesion_id y fecha_inicio; consultar/guardar según caso acordado

**Pasos:**

1. Preparar lesión y fases
2. Solicitar planificación A*
3. Comparar etapas/costo con referencia
4. Verificar persistencia si guardar=true

**Esperado:** Plan de rehabilitación válido y trazable según referencia

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** Histórico NOT RUN; se debe completar fixture y oráculo antes de automatizar.

### TC-37 — Registro con email superto 30 chars (max validation del código)

**Objetivo:** Verificar: Registro con email superto 30 chars (max validation del código)

**Tipo/categoría:** functional / boundary. **Ruta:** POST /register

**Precondiciones:** Datos restantes válidos e identidades nuevas; Entorno aislado de pruebas; no ejecutar escrituras en gimnasia_unificada

**Datos:** Bordes históricos de email 29/30/31; bordes actuales 49/50/51

**Pasos:**

1. Resolver discrepancia del límite documentado
2. Preparar emails válidos en los bordes acordados
3. Enviar y comprobar persistencia o rechazo por longitud

**Esperado:** Histórico sugiere máximo 30; controlador y Blade actuales permiten 50. Requiere decisión formal

**Cobertura:** pending; **pruebas:** pendiente. **Observación:** No cambiar silenciosamente la expectativa: límite original ambiguo frente a implementación actual de 50.

## Inventario ejecutable

- **QA-API-01** — `tests/api/api.spec.ts` → QA-API-01 Login real rechaza contraseña incorrecta; modo browser; casos TC-03.
- **QA-API-02** — `tests/api/api.spec.ts` → QA-API-02 BUG-007 IA POST exige autenticación; modo http-api; casos TC-02, TC-23, TC-24.
- **QA-AUTH-01** — `tests/authentication/authentication.spec.ts` → Un usuario registrado puede iniciar sesión correctamente; modo browser; casos TC-02, TC-04.
- **QA-AUTH-02** — `tests/authentication/authentication.spec.ts` → Credenciales inválidas muestran un mensaje de error; modo browser; casos escenario adicional sin equivalente exacto en los 37.
- **QA-AUTHZ-01** — `tests/authorization/authorization.spec.ts` → Un usuario sin rol administrativo recibe acceso denegado a /admin/reportes; modo browser; casos TC-05, TC-12.
- **QA-AUTHZ-02** — `tests/authorization/authorization.spec.ts` → Un usuario con rol de juez conserva acceso al panel de revisión; modo browser; casos escenario adicional sin equivalente exacto en los 37.
- **QA-FUNC-01** — `tests/functional/functional.spec.ts` → El login exitoso redirige al panel del administrador; modo browser; casos TC-04.
- **QA-FUNC-02** — `tests/functional/functional.spec.ts` → El logout cierra la sesión y vuelve al login; modo browser; casos TC-33.
- **QA-INT-01** — `tests/integration/integration.spec.ts` → QA-INT-01 Login y recurso protegido integran sesión y backend; modo browser; casos TC-04.
- **QA-REG-01** — `tests/regression/regression.spec.ts` → Tras corregir el defecto, el admin conserva acceso autorizado; modo browser; casos TC-04.
- **QA-SEC-001** — `tests/security/security.spec.ts` → BUG-001 Script original de diagnóstico no expone datos; modo http-api; casos TC-11.
- **QA-SEC-002** — `tests/security/security.spec.ts` → BUG-002 Ampersand legítimo conserva integridad (TC-03); modo phpunit-sqlite; casos TC-06, TC-10.
- **QA-SEC-003** — `tests/security/security.spec.ts` → BUG-003 Gimnasta no obtiene reportes, PDF ni panel juez; modo browser; casos TC-05, TC-12, TC-13, TC-14.
- **QA-SEC-004** — `tests/security/security.spec.ts` → BUG-004 Traversal autenticado en descarga de backups (TC-06); modo browser-and-phpunit; casos TC-16, TC-17.
- **QA-SEC-005** — `tests/security/security.spec.ts` → BUG-005 Gimnasta no aprueba solicitud ajena pendiente (TC-07); modo phpunit-sqlite; casos TC-19.
- **QA-SEC-006** — `tests/security/security.spec.ts` → BUG-006 Error real del chatbot no expone trazas; modo browser-and-phpunit; casos TC-21, TC-22.
- **QA-SEC-007** — `tests/security/security.spec.ts` → BUG-007 IA GET exige autenticación; modo http-api; casos escenario adicional sin equivalente exacto en los 37.
- **QA-SEC-008** — `tests/security/security.spec.ts` → BUG-008 Juez rechaza PHP en endpoint original (TC-11); modo phpunit-sqlite; casos TC-32.
- **QA-REG-002-LIVE** — `tests/validation/bug002-live.check.ts` → QA-REG-002-LIVE Registro real normal y con & persiste en MySQL de pruebas; modo browser-and-mysql-readback; casos TC-06, TC-10.

No se implementaron como tests vacíos los casos pendientes ni se usaron skip. El script de validación comprueba integridad del catálogo y referencias reales, no resultados funcionales.
