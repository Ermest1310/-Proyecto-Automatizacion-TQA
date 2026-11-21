import {defineConfig} from '@playwright/test';
import crypto from 'node:crypto';
const {appUrl}=require('./config/runtime.cjs');
const reportDir=process.env.QA_REPORT_DIR||'reports/performance-'+new Date().toISOString().replace(/[:.]/g,'-')+'-'+crypto.randomBytes(4).toString('hex');
export default defineConfig({testDir:'./tests/performance',timeout:35000,workers:1,fullyParallel:false,retries:0,outputDir:reportDir+'/evidence',reporter:[['json',{outputFile:reportDir+'/results.json'}]],use:{baseURL:appUrl(),trace:'off',video:'off'},projects:[{name:'http'}]});
