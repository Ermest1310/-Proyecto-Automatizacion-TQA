# Rendimiento y carga ligera — criterios v1

Los perfiles de `config/performance-profiles.json` están en el catálogo y disponibles desde la web, API y CLI. Son controles de regresión breves para el entorno QA local; no estiman capacidad de producción, estrés máximo ni resistencia de larga duración. Los presupuestos son criterios iniciales explícitos del proyecto, sujetos a revisión formal, sin ajustarlos para ocultar resultados fallidos.

| ID | Objetivo | Solicitudes | Concurrencia máx. | Ritmo máx. | Duración límite | p95 | Errores |
|---|---|---:|---:|---:|---:|---:|---:|
| QA-PERF-001 | Latencia base | 10 | 1 | 2/s | 12 s | ≤ 1500 ms | 0 % |
| QA-LOAD-001 | Carga ligera | 24 | 3 | 4/s | 18 s | ≤ 2500 ms | ≤ 1 % |
| QA-PERF-002 | Estabilidad corta | 30 | 2 | 2/s | 22 s | ≤ 2000 ms | ≤ 1 % |

Estabilidad exige además mediana del último tercio ≤ 2 × mediana del primer tercio + 100 ms. Las ventanas se ordenan por solicitud emitida. Con 24 o 30 peticiones, un solo error ya supera el 1 %. Los tres perfiles completos suman 64 lecturas, ejecutadas secuencialmente.

## Seguridad de ejecución

- Solo GET `/login`, sin autenticación de usuarios ni escrituras de negocio, y sin seguir redirecciones.
- Solo HTTP local (127.0.0.1, localhost o ::1), acreditado previamente con health autenticado que confirma `gimnasia_unificada_test`. Las comprobaciones de identidad son tráfico de control adicional, fuera de las muestras de carga.
- Límites de código: 3 concurrentes, 40 peticiones por perfil, 4/s, 25 s por perfil, 5 s por petición, 256 KiB por respuesta. No son parámetros editables por la API ni la web. El validador rechaza perfiles que los exceden.
- Un fallo de identidad o agotamiento del tiempo global genera BLOCKED/NOT_RUN de infraestructura. Una respuesta errónea, 404, redirección, contenido ajeno o timeout de petición cuenta como error del perfil; jamás lo aprueba.
- Se verifica HTTP 200, HTML y los marcadores reales del formulario de login. Un 200 genérico no basta.
- La cola del ejecutor conserva su serialización, cancelación y límites previos; el runner no usa shell.

## Métricas y evidencia

Tiempo medido desde el inicio de la petición hasta leer y validar el cuerpo, sin incluir la espera intencional del limitador. Se guardan p50/p95/p99 por rango más cercano, media, máximo, duración, throughput y concurrencia observada. Throughput = número de respuestas / duración medida: puede ser ligeramente superior al ritmo nominal en muestras cortas por no existir espera previa a la primera petición. El ritmo se limita mediante separación de inicios, sin ráfagas de recuperación.

Se guardan muestras individuales sin cuerpos, cookies ni credenciales; `performance-metrics.json`, `samples.json` y aserciones quedan en evidencia del caso. El reporter integra métricas en `results.json` y `run.json` para la UI. Cada criterio tiene valor, límite y aprobación explícita. Con muestras pequeñas, p95 y p99 son aproximaciones de regresión, no estimaciones estadísticas robustas.

```powershell
npm run qa:run -- category performance http
npm run qa:run -- category load http
npm run qa:run -- case QA-PERF-001,QA-LOAD-001,QA-PERF-002 http
```

El backend debe estar iniciado. Para ejecución directa sin backend: `npm run qa:performance`; genera carpeta nueva `reports/performance-*`. Sin backend, el comando devuelve fallo cuando un criterio falla. Para documentación y evidencias uniformes, se recomienda la web/API.

## Primera medición real: 16/11/2026

Servidor Laravel de desarrollo PHP local, MySQL de pruebas; carga breve de lectura. Los tiempos dependen también del sistema anfitrión y la serialización del servidor PHP. El fallo observado no demuestra por sí solo un defecto de rendimiento en producción.

| Caso | Estado | Respuestas válidas | p95 real | Presupuesto |
|---|---|---:|---:|---:|
| QA-PERF-001 | PASS | 10/10 | 822,8 ms | 1500 ms |
| QA-LOAD-001 | PASS | 24/24 | 555,8 ms | 2500 ms |
| QA-PERF-002 | FAIL | 30/30 | 2880,4 ms | 2000 ms |

Los 64 cuerpos fueron válidos y la tasa de error fue cero. La mediana de estabilidad cumplió; falló únicamente su p95. Se conserva ese FAIL sin elevar el presupuesto ni introducir reintentos. Queda pendiente investigar el pico bajo condiciones controladas y, antes de extrapolar capacidad, medir sobre un servidor representativo. Los IDs y JSON de la ejecución están en el informe de fases 5 y 6.
