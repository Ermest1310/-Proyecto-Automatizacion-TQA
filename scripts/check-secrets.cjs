// Escanea el proyecto buscando valores de secretos activos (.env.local) y
// claves de proveedor con formato conocido. No imprime nunca el valor, solo
// la ruta del fichero y la clave de entorno o el patrón que coincide.
const fs = require('node:fs');
const path = require('node:path');

// Carga .env.local en process.env (el script original no lo hacía, así que
// buscaba una lista de secretos vacía y nunca encontraba nada).
const runtime = require('../config/runtime.cjs');

const SECRET_KEY = /(?:PASSWORD|TOKEN|SECRET|API_KEY|APP_KEY)$/i;
const IGNORED_VALUES = new Set(['true', 'false', 'off', 'on', 'local', 'smtp', 'starttls', 'none']);
const MIN_SECRET_LENGTH = 6;

// Genera un mapa valor -> nombres de clave para poder informar sin exponerlo.
const secretsByValue = new Map();
for (const [key, value] of Object.entries(process.env)) {
  if (!value || !SECRET_KEY.test(key) || value.length < MIN_SECRET_LENGTH) continue;
  if (IGNORED_VALUES.has(value.toLowerCase())) continue;
  const keys = secretsByValue.get(value) ?? [];
  keys.push(key);
  secretsByValue.set(value, keys);
}

// Formatos de proveedor de alta señal: se detectan aunque la clave ya no esté
// en el entorno, porque siguen siendo válidas si se filtraron.
const PROVIDER_PATTERNS = [
  { name: 'google-api-key', regex: /AIza[0-9A-Za-z_-]{35}/g },
  { name: 'openai-key', regex: /sk-[A-Za-z0-9_-]{20,}/g },
  { name: 'github-token', regex: /gh[pousr]_[A-Za-z0-9]{36,}/g },
  { name: 'aws-access-key', regex: /AKIA[0-9A-Z]{16}/g }
];

const SKIP_DIRS = new Set([
  'node_modules', '.git', 'reports', 'test-results', 'playwright-report', 'blob-report',
  'coverage', 'browsers', 'npm-cache', '.venv', 'venv', 'dist', 'build'
]);
const TEXT_FILE = /\.(?:ts|tsx|cjs|mjs|js|jsx|php|py|json|ya?ml|md|txt|ini|conf|sh|ps1|html|css|scss|xml|lock)$/i;
const MAX_BYTES = 2 * 1024 * 1024;

const violations = [];
const providerFindings = [];
const unreadableFiles = [];
let checkedFiles = 0;
let skippedFiles = 0;

function scanFile(file) {
  let stat;
  try {
    stat = fs.statSync(file);
  } catch (error) {
    unreadableFiles.push({ file: path.relative(runtime.root, file), code: error.code ?? error.message });
    return;
  }
  if (!stat.isFile() || stat.size > MAX_BYTES) {
    skippedFiles++;
    return;
  }

  let text;
  try {
    text = fs.readFileSync(file, 'utf8');
  } catch (error) {
    unreadableFiles.push({ file: path.relative(runtime.root, file), code: error.code ?? error.message });
    return;
  }

  checkedFiles++;
  const relative = path.relative(runtime.root, file);
  for (const [value, keys] of secretsByValue) {
    if (text.includes(value)) violations.push({ file: relative, reason: 'valor-de-entorno', keys });
  }
  for (const { name, regex } of PROVIDER_PATTERNS) {
    regex.lastIndex = 0;
    if (regex.test(text)) providerFindings.push({ file: relative, pattern: name });
  }
}

function walk(dir) {
  let entries;
  try {
    entries = fs.readdirSync(dir, { withFileTypes: true });
  } catch (error) {
    unreadableFiles.push({ file: path.relative(runtime.root, dir), code: error.code ?? error.message });
    return;
  }
  for (const entry of entries) {
    if (entry.isSymbolicLink()) {
      skippedFiles++;
      continue;
    }
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      if (SKIP_DIRS.has(entry.name)) continue;
      walk(full);
      continue;
    }
    // .env.local contiene los secretos por diseño; el resto de .env* tampoco
    // se publica y suele estar vacío, así que no aporta escanearlos.
    if (entry.name.startsWith('.env')) continue;
    if (!TEXT_FILE.test(entry.name)) {
      skippedFiles++;
      continue;
    }
    scanFile(full);
  }
}

walk(runtime.root);

const found = violations.length + providerFindings.length;
console.log(JSON.stringify({
  checkedFiles,
  skippedFiles,
  unreadableFiles,
  secretValuesPrinted: false,
  violations,
  providerPatternFindings: providerFindings,
  ok: found === 0
}, null, 2));

if (found > 0) process.exitCode = 1;
