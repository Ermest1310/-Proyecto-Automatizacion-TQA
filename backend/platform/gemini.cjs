const DEFAULT_MODEL = 'gemini-3.5-flash-lite';
function settings() {
  const model = process.env.QA_GEMINI_MODEL || DEFAULT_MODEL;
  if (!/^gemini-[a-zA-Z0-9.-]{1,80}$/.test(model)) throw Error('Modelo Gemini no válido');
  return { model, key: process.env.GEMINI_API_KEY || '' };
}
async function generate(context, instructions, fetcher = fetch) {
  const { model, key } = settings();
  if (!key) throw Object.assign(Error('Gemini no configurado'), { code: 'not_configured' });
  const response = await fetcher('https://generativelanguage.googleapis.com/v1beta/models/' + model + ':generateContent', {
    method: 'POST', redirect: 'error', signal: AbortSignal.timeout(45000),
    headers: { 'Content-Type': 'application/json', 'x-goog-api-key': key },
    body: JSON.stringify({ systemInstruction: { parts: [{ text: instructions }] },
      contents: [{ role: 'user', parts: [{ text: JSON.stringify(context) }] }],
      generationConfig: { maxOutputTokens: 2048, candidateCount: 1 } }),
  });
  if (!response.ok) {
    const code = response.status === 429 ? 'quota_exceeded' : [400, 401, 403].includes(response.status) ? 'access_rejected' : response.status === 404 ? 'model_unavailable' : 'unavailable';
    // Never log remote error bodies: they may include credentials or private data.
    throw Object.assign(Error('Gemini no completó la solicitud'), { code });
  }
  const data = await response.json(), candidate = data.candidates?.[0];
  if (data.promptFeedback?.blockReason || candidate?.finishReason !== 'STOP') throw Object.assign(Error('Respuesta incompleta o bloqueada'), { code: 'incomplete' });
  const text = (candidate.content?.parts || []).filter(p => typeof p.text === 'string' && !p.thought).map(p => p.text).join('\n').trim();
  if (!text) throw Object.assign(Error('Respuesta vacía'), { code: 'incomplete' });
  return { text, model };
}
module.exports = { DEFAULT_MODEL, settings, generate };
