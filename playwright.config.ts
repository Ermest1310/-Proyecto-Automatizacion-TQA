const { appUrl } = require('./config/runtime.cjs');
import { defineConfig, devices } from '@playwright/test';

const REPORT_DIR = process.env.QA_REPORT_DIR ?? 'reports';
const APP_URL = appUrl();

export default defineConfig({
  testDir: './tests',
  testIgnore: ['**/performance/**','**/demo/**'],
  timeout: 90000,
  outputDir: `${REPORT_DIR}/playwright`,
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [
    ['html', { outputFolder: `${REPORT_DIR}/html`, open: 'never' }],
    ['list'],
    ['json', { outputFile: `${REPORT_DIR}/results.json` }],
  ],
  use: {
    baseURL: APP_URL,
    trace: process.env.QA_ORCHESTRATED === '1' ? 'off' : 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: process.env.QA_ORCHESTRATED === '1' ? 'off' : 'retain-on-failure',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
    { name: 'webkit', use: { ...devices['Desktop Safari'] } },
  ],
});


