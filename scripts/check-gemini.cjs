require('../config/runtime.cjs');
const { generate } = require('../backend/platform/gemini.cjs');
// Explicit smoke test with synthetic data only; never sends project reports.
generate({ caseId: 'QA-CONNECTION-CHECK', status: 'PASS' }, 'Responde en español con una frase breve confirmando que recibiste el caso sintético.').then(result => {
  console.log('Gemini respondió correctamente. Modelo: ' + result.model);
}).catch(error => {
  const messages = { not_configured: 'Falta GEMINI_API_KEY. Ejecuta npm run qa:configure-gemini.',
    quota_exceeded: 'Cuota agotada o límite temporal de Gemini. Revisa tu cuota en AI Studio.',
    access_rejected: 'Google rechazó el acceso. Revisa la clave y los permisos del proyecto.',
    model_unavailable: 'El modelo configurado no está disponible para esta cuenta.', incomplete: 'Google devolvió una respuesta incompleta o bloqueada.' };
  console.error(messages[error.code] || 'No se pudo completar la conexión con Gemini. Comprueba la red y la configuración.');
  process.exitCode = 1;
});
