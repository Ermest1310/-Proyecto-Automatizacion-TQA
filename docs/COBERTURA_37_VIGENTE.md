# Matriz de cobertura vigente - 37 casos

Cobertura indica implementación del escenario; no equivale a PASS actual. Las decisiones de TC-06 y TC-37 fueron confirmadas por el usuario. TC-34 conserva bloqueo por ausencia de vídeo autorizado y referencia.

| ID | Escenario vigente | Cobertura | Automatización |
|---|---|---|---|
| TC-01 | GET / anónimo → redirección a /login | complete | QA-PLAN-01 |
| TC-02 | GET /login formulario disponible | complete | QA-AUTH-01, QA-API-02 |
| TC-03 | Login con email+nacionalidad+cédula real pero password incorrecta | complete | QA-API-01 |
| TC-04 | Login completo de Administrador (admin.inicio) → sesión admin establecida | complete | QA-AUTH-01, QA-FUNC-01, QA-REG-01, QA-INT-01 |
| TC-05 | Login completo de Gimnasta → GET /gimnasta/dashboard 200 | complete | QA-AUTHZ-01, QA-SEC-003 |
| TC-06 | Registro válido crea postulación pendiente sin crear usuario | complete | QA-SEC-002, QA-REG-002-LIVE, QA-PLAN-06 |
| TC-07 | Registro con nombre "María González" + tilde → íntegro en BD | complete | QA-PLAN-07 |
| TC-08 | Registro con edad 9 (límite inferior −1) | complete | QA-PLAN-08 |
| TC-09 | Registro con cédula duplicada (QA-001 ya existente) | complete | QA-PLAN-09 |
| TC-10 | Registro apellido "Sotomayor & Vargas" | complete | QA-SEC-002, QA-REG-002-LIVE |
| TC-11 | Registre anónimo ejecuta el diagnóstico de postulaciones (`/test_postulaciones.php`) | complete | QA-SEC-001 |
| TC-12 | GIMNASTA accede `/admin/reportes` | complete | QA-SEC-003, QA-AUTHZ-01 |
| TC-13 | GIMNASTA descarga PDF completo (`/admin/reportes/exportar-pdf`) | complete | QA-SEC-003 |
| TC-14 | GIMNASTA accede `/juez/dashboard` | complete | QA-SEC-003 |
| TC-15 | GIMNASTA accede `/admin/audit` | complete | QA-PLAN-15 |
| TC-16 | Admin descarga backup válido (nombre correcto) → SQL entregado | complete | QA-SEC-004 |
| TC-17 | Path traversal `..\..\..\.env` en `descargarBackup` | complete | QA-SEC-004 |
| TC-18 | Admin exporta PDF individual de gimnasta (FPDF) | complete | QA-PLAN-18 |
| TC-19 | GIMNASTA aprueba solicitud de nivel ID=2 | complete | QA-SEC-005 |
| TC-20 | GET /areamedica/psicologia → 200 | complete | QA-PLAN-20 |
| TC-21 | Botón chatbot visible + fetch a `/api/psicologia/chat` presente | complete | QA-SEC-006 |
| TC-22 | POST `/api/psicologia/chat` anónimo | complete | QA-SEC-006, QA-PLAN-22 |
| TC-23 | POST `/api/ia/predecir-deduccion` anónimo | complete | QA-API-02 |
| TC-24 | POST `/api/ia/registrar-feedback` anónimo | complete | QA-API-02 |
| TC-25 | POST /sesiones con Fecha=2020 (pasada) | complete | QA-PLAN-25 |
| TC-26 | POST /sesiones válida 10:00–11:00 futura → fila creada (BD) | complete | QA-PLAN-26 |
| TC-27 | POST /sesiones solapado 10:30–11:30 (conflicto mismo gimnasta) | complete | QA-PLAN-27 |
| TC-28 | POST /sesiones Hora_Inicio=05:59 | complete | QA-PLAN-28 |
| TC-29 | POST /sesiones duración 20 min (< 30) | complete | QA-PLAN-29 |
| TC-30 | POST /sesiones duración 175 min (48 bajo el máx 180) | complete | QA-PLAN-30 |
| TC-31 | POST `/analisis/upload` archivo `.php` → rechazado por mimes | complete | QA-PLAN-31 |
| TC-32 | POST `/juez/store` con archivo `.php` como video | complete | QA-SEC-008 |
| TC-33 | POST /logout con sesión vigente → 302 a login | complete | QA-FUNC-02 |
| TC-34 | Evaluación completa de juez con MediaPipe en navegador | partial | QA-PLAN-34 |
| TC-35 | Rutinas óptimas NBA* (`POST /rutinas/planificar-optima`) | complete | QA-PLAN-35 |
| TC-36 | Planificación de rehabilitación A* | complete | QA-PLAN-36 |
| TC-37 | Registro con correo: máximo 50 caracteres y controles de frontera | complete | QA-PLAN-37 |
