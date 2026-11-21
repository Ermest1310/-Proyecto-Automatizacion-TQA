# Informe QA — Avance Etapa 1 → 5 (Sistema de Automatización)

Fecha: 12/11/2026
Proyecto: Proyecto2 (Laravel) + Automatización QA (E:\qa)

## 1. Objetivo del avance

Poner en funcionamiento el entorno del sistema de automatización de pruebas QA:
servidor Laravel accesible, base de datos de pruebas aislada, usuarios QA y
configuración de Playwright apuntando al entorno correcto.

## 2. Archivos modificados

### Automatización (E:\qa)
| Archivo | Cambio |
|---|---|
| E:\qa/playwright.config.ts | `APP_URL` por defecto → `http://Proyecto2.test` |
| E:\qa/config/environments.ts | `baseURL` y `apiBaseUrl` por defecto → `http://Proyecto2.test` |
| E:\qa/pages/LoginPage.ts | Selectores reales de `resources/views/auth/login.blade.php` |

### Servidor Apache (E:\laragon)
| Archivo | Cambio | Backup |
|---|---|---|
| E:\laragon/laragon/bin/apache/httpd-2.4.62-240904-win64-VS17/conf/httpd.conf | `DocumentRoot` → `E:/laragon/www/Proyecto2/public`; `AllowOverride All` | E:\laragon\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\conf\httpd.conf.bak |
| E:\laragon/etc/apache2/sites-enabled/auto.Proyecto2.test.conf | (sin cambios, ya correcto) | E:\laragon/etc/apache2/sites-enabled/auto.Proyecto2.test.conf.bak |

### Configuración Laravel (E:\laragon/www/Proyecto2)
| Archivo | Cambio | Backup |
|---|---|---|
| E:\laragon/www/Proyecto2/.env | `DB_DATABASE` → `gimnasia_unificada_test` | E:\laragon/www/Proyecto2/.env.qa-backup |

## 3. Configuración aplicada

1. Apache reiniciado con la nueva configuración; sintaxis OK (`httpd -t`).
2. Verificado: `http://Proyecto2.test/` → 200 (Laravel) y `http://Proyecto2.test/login` → 200 (Laravel).
3. Antes del fix: `/login` → 404 y `/` → página de bienvenida de Laragon (docroot incorrecto).
4. Base de datos de pruebas: `gimnasia_unificada_test` creada.
5. Migraciones ejecutadas en `gimnasia_unificada_test`: OK (todas DONE).
6. Roles creados: Administrador, Gimnasta, Entrenador, Medico, Fisioterapeuta, Juez, Invitado.
7. Usuarios QA creados: qa_admin@qa.local (Administrador), qa_judge@qa.local (Juez), qa_athlete@qa.local (Gimnasta), qa_guest@qa.local (Invitado).

## 4. Pruebas ejecutadas

Comando: `npx playwright test --project=chromium` (en E:\qa)

Resultado: **18 tests → 7 passed, 11 failed**.

## 5. Resultados

### PASS (7)
1. API: login con credenciales inválidas → rechazado (400+) ✅
2. Autorización: usuario sin rol admin/juez → acceso denegado a /admin ✅ (BUG-003 parcial)
3. Integración: flujo de autenticación sin errores backend ✅
4. Seguridad BUG-001: visitante no accede a /diagnostico ✅
5. Seguridad BUG-004: no accede a /.env ✅
6. Seguridad BUG-003: usuario sin rol admin/juez → /admin denegado ✅
7. Seguridad BUG-002: sanitización con caracteres válidos ✅

### FAIL (11)
1. BUG-007 (API): `/api/ai/status` → 404 (esperaba 401/403).
2-4. Autenticación: `page.goto('/login')` con timeout en algunos tests.
5-7. Funcional/Regresión: tras login, URL final `/login` (esperaba /dashboard; el admin redirige a /admin/inicio).
8. BUG-005 (seguridad): `POST /api/level-requests/1/approve` → 404 (esperaba 401/403).
9. BUG-006 (seguridad): selector chatbot placeholder no resuelto.
10. BUG-007 (seguridad): `/api/ai/status` → 404 (esperaba 401/403).
11. BUG-008 (seguridad): ruta de archivo placeholder (`E:\tmp\shell_test.php` no existe).

## 6. Problemas pendientes

1. URL base corregida y verificada (ETAPA 1 ✅).
2. Login con usuarios QA no concluye en /dashboard → flujo/expectativas por rol pendientes
   (admin → /admin/inicio según AuthController).
3. Endpoints de IA y aprobación de nivel usan rutas placeholder → deben alinearse con la API real.
4. Selector chatbot pendiente de verificación real.
5. Generar reporte HTML final y evidencias.

## 7. Próximos pasos sugeridos

- Completar refinamiento de selectores/expectativas de tests.
- Ejecutar suite completa (3 navegadores) y reporte HTML.
- Integrar GitHub Actions.
- Automatizar backend con PHPUnit/Laravel Testing.
- Convertir los 18 tests en los 37 casos documentados (incl. TC-34 a TC-37).
