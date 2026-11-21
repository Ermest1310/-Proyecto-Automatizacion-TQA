import fs from 'node:fs';
import path from 'node:path';
import {spawnSync} from 'node:child_process';
const {hash,within}=require('../backend/platform/common.cjs');
export interface SyntheticData {schemaVersion:number;generator:string;execution_id:string;seed:number;namespace:string;demo:{left:number;right:number;expected:number};registration:{surname:string;email:string;document:string}[];}
function loadSyntheticFixtures():SyntheticData {
 const root=process.env.QA_FIXTURE_ROOT,id=process.env.QA_RUN_ID;
 if(process.env.QA_ORCHESTRATED!=='1'||!root||!id||process.env.QA_FIXTURE_FILE!==path.join(root,'data.json'))throw new Error('BLOCKED: fixtures requieren ejecución orquestada');
 const manifest=JSON.parse(fs.readFileSync(within(root,'manifest.json'),'utf8')),copy={...manifest};delete copy.integrity;
 if(hash(copy)!==manifest.integrity||manifest.execution_id!==id||manifest.state!=='prepared')throw new Error('BLOCKED: propiedad de fixtures incumplida');
 const bytes=fs.readFileSync(within(root,'data.json')),entry=manifest.files.find((f:any)=>f.name==='data.json'&&f.execution_id===id);
 if(!entry||hash(bytes)!==entry.sha256)throw new Error('BLOCKED: integridad de datos sintéticos incumplida');
 const data=JSON.parse(bytes.toString('utf8')) as SyntheticData;
 if(data.execution_id!==id||data.generator!=='synthetic-v1'||data.seed!==Number(process.env.QA_TEST_SEED)||!Array.isArray(data.registration)||data.registration.length!==3)throw new Error('BLOCKED: datos sintéticos incompatibles');return data;
}
export function syntheticFixtures():SyntheticData {try{return loadSyntheticFixtures();}catch(error){if(error instanceof Error&&error.message.startsWith('BLOCKED:'))throw error;throw new Error('BLOCKED: no se pudieron validar los fixtures registrados');}}
export function mysqlFixtureAction(action:'claim'|'capture'|'cleanup'|'preview'):{state:string;[key:string]:unknown} {
 syntheticFixtures();const root=process.env.QA_FIXTURE_ROOT!;
 const result=spawnSync(process.env.QA_PHP||'php',[path.resolve(__dirname,'../scripts/mysql-owned-fixtures.php'),action,root],{encoding:'utf8',timeout:20000,maxBuffer:1024*1024,windowsHide:true,shell:false,env:{...process.env,QA_RUN_DIR:path.dirname(root)}});
 if(result.status!==0)throw new Error('BLOCKED: adaptador de fixtures SQL no confirmó '+action+'; se conserva el registro privado');return JSON.parse(result.stdout);
}
