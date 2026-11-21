import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { expect, TestInfo } from '@playwright/test';
const { required } = require('../config/runtime.cjs');

const methods: Record<string, string> = {
  '002': 'test_qa_bug_002_ampersand_registration',
  '004': 'test_qa_bug_004_download_control_and_traversal',
  '005': 'test_qa_bug_005_athlete_cannot_approve_other_athlete',
  '006': 'test_qa_bug_006_controlled_provider_failures',
  '008': 'test_qa_bug_008_php_rejected_with_valid_business_fields',
};

/** Real Laravel HTTP kernel + isolated in-memory SQLite; no MySQL access. */
export async function backendRegression(bug: string, info: TestInfo) {return runIsolated(bug,methods[bug],'QaRegressionTest.php',info);}

export async function planRegression(tc:string,info:TestInfo){if(!/^(01|06|07|08|09|15|18|20|22|25|26|27|28|29|30|31|34|35|36|37)$/.test(tc))throw new Error('TC no permitido');return runIsolated('TC-'+tc,'test_plan_tc_'+tc,'QaPlanTest.php',info);}

/** Broader module coverage using the same Laravel HTTP kernel and isolated SQLite fixture. */
export async function expandedRegression(code:string,info:TestInfo){if(!/^\d{2}$/.test(code))throw new Error('ID de ampliación no permitido');return runIsolated('EXT-'+code,'test_expanded_'+code,'QaPlanTest.php',info);}

/** Targeted CRUD and business-rule cases for the deeper second coverage pass. */
export async function deepRegression(code:string,info:TestInfo){if(!/^\d{3}$/.test(code))throw new Error('ID profundo no permitido');return runIsolated('DEEP-'+code,'test_deep_'+code,'QaPlanTest.php',info);}

async function runIsolated(bug:string,method:string,file:string,info:TestInfo){
  const label=bug.startsWith('TC-')?bug:'BUG-'+bug;
  const source = required('QA_APP_SOURCE');
  const runner = path.join(source, 'vendor/phpunit/phpunit/phpunit');
  if (!fs.existsSync(runner)) throw new Error('BLOCKED: PHPUnit de la aplicación no disponible');
  if (!method) throw new Error('ID de regresión no reconocido');
  const scratch = info.outputPath('backend');
  fs.mkdirSync(scratch, { recursive: true });
  const junit = path.join(scratch, 'junit.xml');
  const result = spawnSync(process.env.QA_PHP ?? 'php', [runner,
    '--configuration', path.join(source, 'phpunit.xml'), '--do-not-cache-result',
    '--filter', method, '--log-junit', junit,
    path.resolve(__dirname, '../tests/backend/'+file),
  ], { encoding: 'utf8', timeout: 90000, maxBuffer: 1024 * 1024,
    env: { ...process.env, QA_APP_SOURCE: source, QA_STORAGE_DIR: scratch,
      APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:',
      SESSION_DRIVER: 'array', CACHE_DRIVER: 'array', MAIL_MAILER: 'array' },
  });
  const output = result.stdout + '\n' + result.stderr;
  await info.attach(label + '-PHPUnit', { body: output, contentType: 'text/plain' });
  for (const file of fs.readdirSync(scratch).filter(f => /\.(json|xml)$/.test(f))) {
    await info.attach(file, { path: path.join(scratch, file), contentType: file.endsWith('.xml') ? 'application/xml' : 'application/json' });
  }
  if (result.error) throw new Error('BLOCKED: no pudo ejecutarse PHPUnit: ' + result.error.message);
  expect(fs.existsSync(junit), 'JUnit debe demostrar que se ejecutó una prueba real').toBe(true);
  const report = fs.readFileSync(junit, 'utf8');
  expect(report).toMatch(/tests="1"/);
  expect(result.status, label + ' Laravel aislado:\n' + output).toBe(0);
}
