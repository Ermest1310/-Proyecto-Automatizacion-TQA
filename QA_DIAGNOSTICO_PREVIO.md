# Diagnóstico previo — 12/11/2026
Se revisaron los 18 tests actuales y las fuentes Laravel sin modificar producción.
El histórico 7 PASS / 11 FAIL no corresponde exactamente al código actual: ya contiene correcciones parciales.
Fuente original: docs/entregable1_pruebas_funcionales.md y docs/analisis_proyecto.md.
BUG-001 debe probar /test_postulaciones.php, no /diagnostico.
BUG-002 debe conservar el falso positivo con &, no sustituirse por XSS en email.
BUG-003 debe probar rutas existentes con sesión confirmada, no /admin inexistente.
BUG-004 debe probar traversal autenticado en descargar-backup, no GET /.env.
BUG-005 requiere solicitud ajena pendiente y comprobación del estado antes/después.
BUG-006 requiere respuesta real del servidor; el eco del usuario no demuestra nada.
BUG-007 debe comprobar JSON 401 y nunca aceptar 404/419/500 como autorización.
BUG-008 corresponde a /juez/store; /analisis/upload era un caso positivo del plan.
Logout usa URL absoluta en Blade: selector debe aceptar sufijo /logout.
Integración y API login actuales tienen aserciones demasiado débiles.
No se ejecutan mutaciones de registro/aprobación sin aislamiento comprobado.
