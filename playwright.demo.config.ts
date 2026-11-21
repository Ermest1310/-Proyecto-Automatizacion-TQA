import {defineConfig,devices} from '@playwright/test';
import crypto from 'node:crypto';
const dir=process.env.QA_REPORT_DIR||'reports/demo-'+Date.now()+'-'+crypto.randomBytes(4).toString('hex');
export default defineConfig({testDir:'./tests/demo',timeout:15000,retries:0,workers:1,fullyParallel:false,outputDir:dir+'/evidence',reporter:[['json',{outputFile:dir+'/results.json'}]],use:{trace:'off',video:'off',screenshot:'off'},projects:[{name:'demo',use:{...devices['Desktop Chrome']}}]});
