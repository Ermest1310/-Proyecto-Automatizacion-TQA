import { test, expect } from '@playwright/test';
import fs from 'node:fs';
const { appUrl,required }=require('../../config/runtime.cjs');
const {runProfile}=require('../../utils/performance.cjs');
const profiles=require('../../config/performance-profiles.json').profiles;
async function execute(id:string,info:any){const profile=profiles.find((p:any)=>p.id===id);const result=await runProfile(profile,appUrl(),required('QA_API_TOKEN'));const file=info.outputPath('performance-metrics.json');fs.writeFileSync(file,JSON.stringify(result.metrics,null,2));await info.attach('performance-metrics',{path:file,contentType:'application/json'});fs.writeFileSync(info.outputPath('samples.json'),JSON.stringify(result.samples,null,2));for(const c of result.metrics.criteria)expect(c.passed,c.name+': '+c.actual+' / límite '+c.limit).toBe(true);}
test('QA-PERF-001 Latencia base de lectura',async({},info)=>{await execute('QA-PERF-001',info);});
test('QA-LOAD-001 Carga ligera de lectura',async({},info)=>{await execute('QA-LOAD-001',info);});
test('QA-PERF-002 Estabilidad corta de lectura',async({},info)=>{await execute('QA-PERF-002',info);});
